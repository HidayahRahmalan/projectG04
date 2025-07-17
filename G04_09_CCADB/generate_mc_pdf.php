<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
require_once 'db_conn.php';
require('fpdf/fpdf.php'); // Include the FPDF library

if (!isset($_GET['appt_id'])) { die("Appointment ID not specified."); }
$appointment_id = intval($_GET['appt_id']);

// Fetch all necessary details for the MC
$sql = "SELECT 
            p.Name as PatientName, p.ICNumber,
            d.Name as DoctorName,
            c.ClinicName, c.Location as ClinicAddress,
            mc.StartDate, mc.NumberOfDays, mc.Reason, mc.IssuedAt
        FROM MedicalCertificate mc
        JOIN Appointment a ON mc.AppointmentID = a.AppointmentNo
        JOIN Patient p ON a.PatientID = p.PatientID
        JOIN Doctor d ON a.DoctorID = d.DoctorID
        JOIN Clinic c ON a.ClinicID = c.ClinicID
        WHERE mc.AppointmentID = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) { die("Medical Certificate not found for this appointment."); }
$data = $result->fetch_assoc();

// --- PDF Generation using FPDF ---

class PDF extends FPDF {
    // Page header
    function Header() {
        // You can add a logo here if you have one
        // $this->Image('path/to/logo.png',10,6,30);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, $GLOBALS['data']['ClinicName'], 0, 1, 'C');
        $this->SetFont('Arial', '', 9);
        // Split address into multiple lines if needed
        $address_lines = explode(',', $GLOBALS['data']['ClinicAddress']);
        foreach($address_lines as $line){
            $this->Cell(0, 5, trim($line), 0, 1, 'C');
        }
        $this->Ln(10); // Line break
    }
}

// Create new PDF instance
$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);

// Title
$pdf->Cell(0, 10, 'MEDICAL CERTIFICATE', 0, 1, 'C');
$pdf->Ln(10);

// Body
$pdf->SetFont('Arial', '', 11);

// Date Issued
$pdf->Cell(30, 7, 'Date / Tarikh:');
$pdf->Cell(100, 7, date('d-m-Y', strtotime($data['IssuedAt'])), 'B'); // Underlined
$pdf->Ln(15);

// Patient Details
$pdf->Cell(40, 7, 'This is to certify that');
$pdf->Cell(100, 7, strtoupper($data['PatientName']), 'B');
$pdf->Ln();
$pdf->Cell(40, 7, 'of');
$pdf->Cell(100, 7, '(IC No: ' . ($data['ICNumber'] ?? 'N/A') . ')', 'B');
$pdf->Ln(15);

// Unfit for Duty
$pdf->Cell(40, 7, 'Will be');
$pdf->Cell(100, 7, 'UNFIT FOR DUTY', 'B');
$pdf->Ln(10);

// Duration
$start_date_obj = new DateTime($data['StartDate']);
$end_date_obj = clone $start_date_obj;
$end_date_obj->modify('+' . ($data['NumberOfDays'] - 1) . ' days');

$pdf->Cell(40, 7, 'For');
$pdf->Cell(100, 7, $data['NumberOfDays'] . ' day(s)', 'B');
$pdf->Ln(10);
$pdf->Cell(40, 7, 'From');
$pdf->Cell(50, 7, $start_date_obj->format('d-m-Y'), 'B');
$pdf->Cell(10, 7, 'to');
$pdf->Cell(50, 7, $end_date_obj->format('d-m-Y'), 'B');
$pdf->Ln(15);

// Doctor and Details
$pdf->Cell(40, 7, 'Doctor on duty');
$pdf->Cell(100, 7, 'DR. ' . strtoupper($data['DoctorName']), 'B');
$pdf->Ln(10);
$pdf->Cell(40, 7, 'Details:');
$pdf->MultiCell(130, 7, $data['Reason'], 'B');
$pdf->Ln(25);

// Signature area
$pdf->Cell(0, 10, '........................................................', 0, 1, 'C');
$pdf->Cell(0, 5, 'DR. ' . strtoupper($data['DoctorName']), 0, 1, 'C');
$pdf->Cell(0, 5, $data['ClinicName'], 0, 1, 'C');


// Output the PDF
$pdf->Output('D', 'MC-' . $appointment_id . '.pdf'); // 'D' prompts download
?>