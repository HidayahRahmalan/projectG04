<?php
require_once 'vendor/autoload.php';
session_start();
include('connect.php'); 

$recipe_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($recipe_id <= 0) {
    die("Invalid recipe ID");
}


$recipe_sql = "SELECT * FROM recipe WHERE Recipe_ID = ?";
$recipe_stmt = $conn->prepare($recipe_sql);
$recipe_stmt->bind_param("i", $recipe_id);
$recipe_stmt->execute();
$recipe_result = $recipe_stmt->get_result();

if ($recipe_result->num_rows == 0) {
    die("Recipe not found");
}

$recipe = $recipe_result->fetch_assoc();


$steps_sql = "SELECT * FROM step WHERE Recipe_ID = ? ORDER BY Step_Number ASC";
$steps_stmt = $conn->prepare($steps_sql);
$steps_stmt->bind_param("i", $recipe_id);
$steps_stmt->execute();
$steps_result = $steps_stmt->get_result();


$images_sql = "SELECT * FROM image WHERE Recipe_ID = ?";
$images_stmt = $conn->prepare($images_sql);
$images_stmt->bind_param("i", $recipe_id);
$images_stmt->execute();
$images_result = $images_stmt->get_result();


$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);


$pdf->SetCreator('Recipe Manager');
$pdf->SetAuthor('Recipe Manager System');
$pdf->SetTitle('Recipe: ' . $recipe['Recipe_Title']);
$pdf->SetSubject('Recipe Details');


$pdf->SetHeaderData('', 0, 'Recipe Details', 'Generated on ' . date('Y-m-d H:i:s'));


$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));


$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);


$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);


$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);


$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);


$pdf->AddPage();


$pdf->SetFont('helvetica', '', 12);


$pdf->SetFont('helvetica', 'B', 20);
$pdf->Cell(0, 15, htmlspecialchars($recipe['Recipe_Title']), 0, 1, 'L');
$pdf->Ln(5);


$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(40, 8, 'Recipe ID:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 8, $recipe['Recipe_ID'], 0, 1, 'L');

$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(40, 8, 'Cuisine Type:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 8, htmlspecialchars($recipe['Recipe_CuisineType']), 0, 1, 'L');

$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(40, 8, 'Dietary Type:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 8, htmlspecialchars($recipe['Recipe_DietaryType']), 0, 1, 'L');

$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(40, 8, 'Upload Date:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 8, $recipe['Recipe_UploadDate'], 0, 1, 'L');

$pdf->Ln(10);


$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Description', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 12);
$pdf->MultiCell(0, 8, htmlspecialchars($recipe['Recipe_Description']), 0, 'L');
$pdf->Ln(10);


if ($images_result->num_rows > 0) {
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'Recipe Images', 0, 1, 'L');
    $pdf->Ln(5);
    
        $image_count = 0;
        $images_per_row = 2;
        $image_width = 50;
        $image_height = 50;
        $cell_padding = 10;

        $current_x = $pdf->GetX();
        $current_y = $pdf->GetY();

        while ($image = $images_result->fetch_assoc()) {
            $image_path = $image['Image_Path'];

            if (file_exists($image_path)) {
                try {
                    $col = $image_count % $images_per_row;
                    $x = $pdf->GetX() + ($col * ($image_width + $cell_padding));
                    $y = $current_y;

                    $pdf->Image($image_path, $x, $y, $image_width, $image_height, '', '', '', false, 300, '', false, false, 1, false, false, false);

                    $image_count++;

                    // satu row penuh, next row
                    if ($image_count % $images_per_row == 0) {
                        $current_y += $image_height + $cell_padding;
                        $pdf->SetY($current_y);
                        $pdf->SetX($current_x);
                    }

                } catch (Exception $e) {
                    $pdf->SetFont('helvetica', 'I', 10);
                    $pdf->Cell(0, 8, 'Image: ' . basename($image_path) . ' (Could not load)', 0, 1, 'L');
                }

            } else {
                $pdf->SetFont('helvetica', 'I', 10);
                $pdf->Cell(0, 8, 'Image: ' . basename($image_path) . ' (File not found)', 0, 1, 'L');
            }
        }

        // make the row proper
        if ($image_count % $images_per_row != 0) {
            $current_y += $image_height + $cell_padding;
            $pdf->SetY($current_y);
        }
    
    if ($image_count == 0) {
        $pdf->SetFont('helvetica', 'I', 10);
        $pdf->Cell(0, 8, 'No images could be displayed', 0, 1, 'L');
    }
    
    $pdf->Ln(10);
}


$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Cooking Instructions', 0, 1, 'L');
$pdf->Ln(5);

if ($steps_result->num_rows > 0) {
    while ($step = $steps_result->fetch_assoc()) {

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'Step ' . $step['Step_Number'] . ':', 0, 1, 'L');
        
        $pdf->SetFont('helvetica', '', 11);
        $pdf->MultiCell(0, 6, htmlspecialchars($step['Step_Instruction']), 0, 'L');
        

        if (!empty($step['Step_ImagePath']) && file_exists($step['Step_ImagePath'])) {
            try {
                $pdf->Image($step['Step_ImagePath'], '', '', 60, 0, '', '', 'T', false, 300, '', false, false, 1, false, false, false);
                $pdf->Ln(5);
            } catch (Exception $e) {
                $pdf->SetFont('helvetica', 'I', 9);
                $pdf->Cell(0, 5, 'Step image could not be loaded.', 0, 1, 'L');
            }
        }
        
        $pdf->Ln(8);
    }
} else {
    $pdf->SetFont('helvetica', 'I', 11);
    $pdf->Cell(0, 8, 'No cooking steps available', 0, 1, 'L');
}


$conn->close();


$filename = 'Recipe_' . $recipe_id . '_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $recipe['Recipe_Title']) . '.pdf';
$pdf->Output($filename, 'I'); // 'I' for inline display, 'D' for download
?>