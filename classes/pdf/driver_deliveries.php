<?php

namespace nrvbd\pdf;

use nrvbd\helpers;

if (!class_exists('\nrv_tools\pdf\driver_deliveries')) {
    class driver_deliveries
    {

		public $font_family = 'Arial';

        public function pdf(string $name = "nom_du_fichier", string $output = 'I', int $numTables = 5)
        {
            // error_reporting(0);
            $pdf = new \FPDF();
            $pdf->AddPage();

            $remainingHeight = $pdf->GetPageHeight() - $pdf->GetY(); 	
			
			$colWidth1 = ($pdf->GetPageWidth() - 20) * 0.6;
			$colWidth2 = ($pdf->GetPageWidth() - 20) * 0.4;
			$contentLineHeight = 7; 

            for($i = 0; $i < $numTables; $i++){
				// Prepare data and calculate table height
				$content_1_1 = 'Contenu ligne 1, 1';
				$content_1_2 = 'Contenu ligne 1, 2';
			
				$content_2_1 = 'Contenu ligne 1, colonne 2 (partie supérieure)';
				$content_2_2 = 'Contenu ligne 2, colonne 2 (partie inférieure) (partie inférieure) (partie inférieure) (partie inférieure)';
				
				$content_1_1_height = $this->simulateMultiCellHeight($colWidth1, $contentLineHeight, $content_1_1);
				$content_1_2_height = $this->simulateMultiCellHeight($colWidth1, $contentLineHeight, $content_1_2);
				$content_2_1_height = $this->simulateMultiCellHeight($colWidth2, $contentLineHeight, $content_2_1);
				$content_2_2_height = $this->simulateMultiCellHeight($colWidth2, $contentLineHeight, $content_2_2);
	
				$column_2_height = $content_2_1_height + $content_2_2_height;
				$column_1_height = max($content_1_1_height, $content_1_2_height);
				$tableHeight = max($column_1_height, $column_2_height) + 20;
				if($column_1_height > $column_2_height){
					$hcol1 = $column_1_height;
					$hcol21 = $content_2_1_height;
					$hcol22 = $hcol1 - $hcol21;
				}elseif($column_1_height < $column_2_height){
					$hcol1 = $column_2_height;
					$hcol21 = $content_2_1_height;
					$hcol22 = $content_2_2_height;
				}
				

				// Add page if necessary
				if($remainingHeight - $tableHeight < 0){
					$pdf->AddPage();
					$remainingHeight = $pdf->GetPageHeight() - $pdf->GetY(); 
				}
				
				// Generate table
				$this->generateTable($pdf, 
									 $content_1_1, 
									 $content_1_2, 
									 $content_2_1, 
									 $content_2_2, 
									 $hcol1, 
									 $hcol21, 
									 $hcol22, 
									 $contentLineHeight, 
									 $colWidth1, 
									 $colWidth2);

				// Update remaining height
				$remainingHeight -= $tableHeight;
            }

            $pdf->Output($output, $name . '.pdf');
        }


		private function generateTable(&$pdf, $content_1_1, $content_1_2, $content_2_1, $content_2_2, $hcol1, $hcol21, $hcol22, $contentLineHeight, $colWidth1, $colWidth2)
		{
			$lineHeight = 10; 
			$pdf->SetFont('Arial', '', 8);
			$pdf->SetFillColor(255, 255, 255);
		
			// Colonne 1 - Rectangle en arrière-plan
			$header_y = $pdf->GetY();
			$pdf->SetDrawColor(0, 0, 0);
			$pdf->Rect(10, $header_y, $colWidth1, $lineHeight, 'D');
		
			$pdf->SetXY(10, $header_y);
			$pdf->SetFont('Arial', 'B', 10);
			$pdf->MultiCell($colWidth1, $lineHeight, 'aaa', '', 'C');
		
			// Colonne 2 - Rectangle en arrière-plan
			$pdf->SetXY(10 + $colWidth1, $header_y);
			$pdf->SetDrawColor(0, 0, 0);
			$pdf->Rect(10 + $colWidth1, $header_y, $colWidth2, $lineHeight, 'D');		
			$pdf->MultiCell($colWidth2, $lineHeight, '#', '', 'C');
		
			$pdf->setY($header_y);			
			$pdf->SetFont('Arial', '', 10);

			// Contenu des colonnes			
			// $lineHeight = 7; 
			// $content_1_1 = 'Contenu ligne 1, 1';
			// $content_1_2 = 'Contenu ligne 1, 2';
		
			// $content_2_1 = 'Contenu ligne 1, colonne 2 (partie supérieure)';
			// $content_2_2 = 'Contenu ligne 2, colonne 2 (partie inférieure) (partie inférieure) (partie inférieure) (partie inférieure)';
			
			// $content_1_1_height = $this->simulateMultiCellHeight($colWidth1, $lineHeight, $content_1_1);
			// $content_1_2_height = $this->simulateMultiCellHeight($colWidth1, $lineHeight, $content_1_2);
			// $content_2_1_height = $this->simulateMultiCellHeight($colWidth2, $lineHeight, $content_2_1);
			// $content_2_2_height = $this->simulateMultiCellHeight($colWidth2, $lineHeight, $content_2_2);

			// $column_2_height = $content_2_1_height + $content_2_2_height;
			// $column_1_height = max($content_1_1_height, $content_1_2_height);
			// if($column_1_height > $column_2_height){
			// 	$hcol1 = $column_1_height;
			// 	$hcol21 = $content_2_1_height;
			// 	$hcol22 = $hcol1 - $hcol21;
			// }elseif($column_1_height < $column_2_height){
			// 	$hcol1 = $column_2_height;
			// 	$hcol21 = $content_2_1_height;
			// 	$hcol22 = $content_2_2_height;
			// }
			
			$pdf->SetDrawColor(0, 0, 0); 		
			// Colonne 1
			$y = $pdf->GetY() + 10;
			$pdf->SetXY(10, $y);
			$pdf->Rect(10, $y, $colWidth1 / 2, $hcol1, 'D');
			$pdf->MultiCell($colWidth1 / 2, $contentLineHeight, $content_1_1, '', 'C');
		
			$pdf->SetXY(10 + $colWidth1 / 2, $y);
			$pdf->Rect(10 + $colWidth1 / 2, $y, $colWidth1 / 2, $hcol1, 'D'); 
			$pdf->MultiCell($colWidth1 / 2, $contentLineHeight, $content_1_2, '', 'C');
		
			// Colonne 2
			$pdf->SetXY(10 + $colWidth1, $y);
			$pdf->Rect(10 + $colWidth1, $y, $colWidth2, $hcol21, 'D');
			$pdf->MultiCell($colWidth2, $contentLineHeight, $content_2_1, '', 'C');
			$pdf->ln(10);

			$pdf->SetXY(10 + $colWidth1, $pdf->GetY());
			$pdf->Rect(10 + $colWidth1, $pdf->GetY(), $colWidth2, $hcol22, 'D');
			$pdf->MultiCell($colWidth2, $contentLineHeight, $content_2_2, '', 'C');
			$pdf->ln(10);
			$pdf->ln(10);

			return $pdf->GetY();
		}

		

		/**
		 * Undocumented function
		 * @param [type] $pdf
		 * @param [type] $width
		 * @param [type] $height
		 * @param [type] $text
		 * @return int
		 */
        private function simulateMultiCellHeight($width, $height, $text)
        {
            $tempPdf = new \FPDF();
            $tempPdf->AddPage();
            $tempPdf->SetFont($this->font_family, "B", 10);
            $tempPdf->MultiCell($width, $height, $text);
			return $tempPdf->getY();
        }


		// private function MultiCellRow($cells, $width, $height, $data, $pdf)
		// {
		// 	$x = $pdf->GetX();
		// 	$y = $pdf->GetY();
		// 	$maxheight = 0;
		
		// 	for ($i = 0; $i < $cells; $i++) {
		// 		$pdf->MultiCell($width, $height, $data[$i]);
		// 		if ($pdf->GetY() - $y > $maxheight) $maxheight = $pdf->GetY() - $y;
		// 		$pdf->SetXY($x + ($width * ($i + 1)), $y);
		// 	}
		
		// 	for ($i = 0; $i < $cells + 1; $i++) {
		// 		$pdf->Line($x + $width * $i, $y, $x + $width * $i, $y + $maxheight);
		// 	}
		
		// 	$pdf->Line($x, $y, $x + $width * $cells, $y);
		// 	$pdf->Line($x, $y + $maxheight, $x + $width * $cells, $y + $maxheight);
		// }
    }
}
