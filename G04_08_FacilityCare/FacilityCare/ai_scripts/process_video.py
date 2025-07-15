import sys
import os
import subprocess
import whisper
import openai
import logging
from pathlib import Path
from dotenv import load_dotenv 

# Load .env file
load_dotenv()

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    filename='video_processing.log'
)
logger = logging.getLogger()

# Configuration
# Get API key from .env
openai.api_key = os.getenv("OPENAI_API_KEY")
WHISPER_MODEL = "base"  # Use "small" if "base" isn't available
FFMPEG_PATH = "ffmpeg"  # Make sure ffmpeg is in your PATH

def extract_audio(video_path, audio_path):
    """Extract audio using FFmpeg directly"""
    try:
        cmd = [
            FFMPEG_PATH,
            '-y',  # Overwrite without asking
            '-i', video_path,
            '-vn',  # No video
            '-acodec', 'pcm_s16le',  # WAV format
            '-ar', '16000',  # Sample rate
            '-ac', '1',  # Mono channel
            audio_path
        ]
        
        logger.info(f"Running FFmpeg command: {' '.join(cmd)}")
        result = subprocess.run(cmd, check=True, capture_output=True, text=True)
        logger.info(f"FFmpeg output: {result.stdout}")
        return True
    except subprocess.CalledProcessError as e:
        logger.error(f"FFmpeg failed: {e.stderr}")
        return False
    except Exception as e:
        logger.error(f"Audio extraction error: {str(e)}")
        return False

def transcribe_audio(audio_path):
    """Transcribe audio using Whisper"""
    try:
        logger.info("Loading Whisper model...")
        model = whisper.load_model(WHISPER_MODEL)
        logger.info("Starting transcription...")
        result = model.transcribe(audio_path)
        return result["text"]
    except Exception as e:
        logger.error(f"Transcription failed: {str(e)}")
        return None

def summarize_text(text):
    """Generate summary using OpenAI"""
    try:
        logger.info("Generating summary...")
        response = openai.ChatCompletion.create(
            model="gpt-3.5-turbo",
            messages=[
                {"role": "system", "content": "You are an assistant that summarizes maintenance reports in 2-3 concise sentences."},
                {"role": "user", "content": f"Summarize this maintenance report: {text}"}
            ],
            temperature=0.3
        )
        return response['choices'][0]['message']['content']
    except Exception as e:
        logger.error(f"OpenAI API error: {str(e)}")
        return None

def main(video_path):
    """Main processing function"""
    try:
        video_path = os.path.abspath(video_path)
        base_name = os.path.splitext(video_path)[0]
        audio_path = f"{base_name}.wav"
        transcript_path = f"{base_name}_transcript.txt"
        summary_path = f"{base_name}_summary.txt"

        logger.info(f"Processing video: {video_path}")

        # Step 1: Extract audio
        if not extract_audio(video_path, audio_path):
            raise Exception("Audio extraction failed")

        # Step 2: Transcribe
        transcript = transcribe_audio(audio_path)
        if not transcript:
            raise Exception("Transcription failed")

        # Step 3: Summarize
        summary = summarize_text(transcript)
        if not summary:
            summary = "Could not generate summary"

        # Save results
        with open(transcript_path, 'w', encoding='utf-8') as f:
            f.write(transcript)
        
        with open(summary_path, 'w', encoding='utf-8') as f:
            f.write(summary)

        logger.info("Processing completed successfully")
        return True

    except Exception as e:
        logger.error(f"Processing failed: {str(e)}")
        return False

if __name__ == "__main__":
    if len(sys.argv) != 2:
        print("Usage: python process_video.py <video_file.mp4>")
        sys.exit(1)
    
    success = main(sys.argv[1])
    sys.exit(0 if success else 1)