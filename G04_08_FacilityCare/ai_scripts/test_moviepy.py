try:
    from moviepy.editor import VideoFileClip
    print("MoviePy imported successfully!")
except Exception as e:
    print(f"Error: {e}")