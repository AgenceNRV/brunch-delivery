<?php

namespace nrvbd\pdf;

use nrvbd\helpers;

if (!class_exists('\nrvbd\pdf\kitchen_notes')) {
    class kitchen_notes
    {

		public $font_family = 'Arial';

		public $data;

		public $delivery_date;

		private $col_1_width;

		private $col_2_width;

		private $line_height = 10;

		private $content_line_height = 7;


		public function __construct(string $delivery_date, 
								    array $data = array())
		{
			$this->data = $data;
			$this->delivery_date = $delivery_date;
		}


        public function save(string $name, 
							 string $output = 'I')
        {
            error_reporting(0);
            $pdf = new \FPDF();
			
			$this->col_1_width = ($pdf->GetPageWidth() - 20) * 0.8;
			$this->col_2_width = ($pdf->GetPageWidth() - 20) * 0.2;
			
			$prepared_data = $this->process_data();

			$pdf->AddPage();
			$this->generate_header($pdf);

			foreach($prepared_data as $key => $data){
				if($key == "extra"){
					$this->generate_extra_table($pdf, $data);
				}else{
					$this->generate_addon_table($pdf, $data);
				}
				
				$pdf->Ln(10);
			}
            return $pdf->Output($output, $name);
        }


		/**
		 * Generate the header
		 * @method generate_header
		 * @param  fpdf   $pdf
		 * @return void
		 */
		private function generate_header(&$pdf)
		{
			$pdf->SetFont('Arial', 'B', 16);
			$pdf->SetFillColor(90, 58, 34);
			$pdf->SetDrawColor(0, 0, 0);
			$pdf->SetTextColor(255,255,255);
			$pdf->Rect(10, 10, $pdf->GetPageWidth() - 20, 20, 'D');
			$pdf->SetXY(10, 10);
			$pdf->MultiCell($pdf->GetPageWidth() - 20, 20, nrvbd_pdf_text("Préparations pour le " . $this->delivery_date), '', 'C', true);
			$pdf->ln(10);
		}



		/**
		 * Generate the addon table
		 * @method generate_addon_table
		 * @param  fpdf $pdf
		 * @param  array $data
		 * @return float
		 */
		private function generate_addon_table(&$pdf, $data)
		{			
            $remaining_height = $pdf->GetPageHeight() - $pdf->GetY(); 	

			if($remaining_height - $pdf->GetY() - 30 < 0){
				$pdf->AddPage();
				$remaining_height = $pdf->GetPageHeight() - $pdf->GetY(); 
			}

			$y = $pdf->GetY();
			$pdf->SetFont('Arial', 'B', 10);
			$pdf->SetFillColor(238, 211, 189);
      		$pdf->SetTextColor(0, 0, 0);
			$pdf->SetDrawColor(0, 0, 0);
			$pdf->Rect(10, $y, $pdf->GetPageWidth() - 20, $this->line_height, 'D');		
			$pdf->SetXY(10, $y);
			$pdf->MultiCell($pdf->GetPageWidth() - 20,
							$this->line_height, 
							nrvbd_pdf_text($data['name'] . ' x' . $data['quantity']), 
							'', 
							'C', 
							true);

			
			$y = $pdf->GetY();
			$pdf->SetDrawColor(0, 0, 0);
			$pdf->SetFillColor(255, 248, 225);
			$pdf->Rect(10, $y, $this->col_1_width, $this->line_height, 'D');		
			$pdf->SetXY(10, $y);
			$pdf->SetFont('Arial', 'B', 10);
			$pdf->MultiCell($this->col_1_width, $this->line_height, nrvbd_pdf_text("Produit"), '', 'L', true);
			$pdf->SetXY(10 + $this->col_1_width, $y);
			$pdf->SetDrawColor(0, 0, 0);
			$pdf->Rect(10 + $this->col_1_width, $y, $this->col_2_width, $this->line_height, 'D');		
			$pdf->MultiCell($this->col_2_width, $this->line_height, nrvbd_pdf_text("Quantité"), '', 'R', true);

			$pdf->SetFillColor(238, 211, 189);
      		$pdf->SetTextColor(0,0,0);
			$pdf->SetFont($this->font_family, 'B', 10);			
			
			foreach($data['addons'] as $addon_category => $addons){
				$height = $this->calculate_table_addon_category_height($addon_category, $addons);
				if($remaining_height - $height - 30 < 0){
					$pdf->AddPage();
					$remaining_height = $pdf->GetPageHeight() - $pdf->GetY(); 
				}
				$this->table_addon_category($pdf, $height, $addon_category, $addons);				
				$remaining_height = $pdf->GetPageHeight() - $pdf->GetY();
			}
			
		}


		/**
		 * Generate the table for the addons
		 * @method table_addon_category
		 * @param  fpdf   $pdf
		 * @param  float  $height
		 * @param  string $addon_category
		 * @param  array  $addon_data
		 * @return void
		 */
		private function table_addon_category(&$pdf, 
											  $height, 
											  $addon_category, 
											  $addon_data)
		{
			$pdf->SetFont($this->font_family, 'B', 10);

			// Colonne 1
			$y = $pdf->getY();
			$pdf->SetFillColor(255, 255, 255);
			$pdf->SetDrawColor(0, 0, 0);
			$pdf->SetXY(10, $y);
			$pdf->Cell($this->col_1_width, 
					   $this->content_line_height+3,
					   nrvbd_pdf_text($addon_category), 
					   0, 
					   0,
					   'L');

			$pdf->SetXY(10, $y);
			$pdf->Rect(10, $y, $this->col_1_width, $height, 'D');			
			
			// Colonne 2
			$pdf->SetXY(10 + $this->col_1_width, $y);
			$pdf->Rect(10 + $this->col_1_width, $y, $this->col_2_width, $height, 'D');
			$pdf->Cell($this->col_2_width,
					   $this->content_line_height,
					   nrvbd_pdf_text(""), 
					   0, 
					   1, 
					   'R');

			$pdf->SetFont($this->font_family, '', 10);
			foreach($addon_data as $addon_name => $addon_quantity){
				$pdf->Cell($this->col_1_width, 
						   $this->content_line_height, 
						   nrvbd_pdf_text($addon_name), 
						   0, 
						   0, 
						   'L');
				$pdf->Cell($this->col_2_width, 
						   $this->content_line_height, 
						   nrvbd_pdf_text($addon_quantity), 
						   0, 
						   1, 
						   'R');
			}
			
			$pdf->setY($y + $height);
		}


		/**
		 * Calculate the height of the table for the addons
		 * @method calculate_table_addon_category_height
		 * @param  string $addon_category
		 * @param  array $addon_data
		 * @return float
		 */
		private function calculate_table_addon_category_height($addon_category, $addon_data)
		{
            $tempPdf = new \FPDF();
            $tempPdf->AddPage();
			$tempPdf->SetFont($this->font_family, 'B', 10);

			// Colonne 1
			$y = $tempPdf->getY();
			$tempPdf->SetFillColor(255, 255, 255);
			$tempPdf->SetDrawColor(0, 0, 0);
			$tempPdf->SetXY(10, $y);
			$tempPdf->Cell($this->col_1_width, 
					   $this->content_line_height+3,
					   nrvbd_pdf_text($addon_category), 
					   0, 
					   0,
					   'L');

			$tempPdf->SetXY(10, $y);			
			
			// Colonne 2
			$tempPdf->SetXY(10 + $this->col_1_width, $y);
			$tempPdf->Cell($this->col_2_width,
					   $this->content_line_height,
					   nrvbd_pdf_text(""), 
					   0, 
					   1, 
					   'R');

			$tempPdf->SetFont($this->font_family, '', 10);
			foreach($addon_data as $addon_name => $addon_quantity){
				$tempPdf->Cell($this->col_1_width, 
							   $this->content_line_height, 
							   nrvbd_pdf_text($addon_name), 
							   0, 
							   0, 
							   'L');
				$tempPdf->Cell($this->col_2_width, 
							   $this->content_line_height, 
							   nrvbd_pdf_text($addon_quantity), 
							   0, 
							   1, 
							   'R');
			}
			return $tempPdf->getY();
		}


		private function generate_extra_table(&$pdf, $data)
		{
			$pdf->AddPage();
		
			$y = $pdf->GetY();
			$pdf->SetFont('Arial', 'B', 10);
			$pdf->SetFillColor(238, 211, 189);
			$pdf->SetTextColor(0, 0, 0);
			$pdf->SetDrawColor(0, 0, 0);
			$pdf->Rect(10, $y, $pdf->GetPageWidth() - 20, $this->line_height, 'D');
			$pdf->SetXY(10, $y);
			$pdf->MultiCell($pdf->GetPageWidth() - 20, $this->line_height, nrvbd_pdf_text("Les Extras pour le " . $this->delivery_date), '', 'C', true);
		
			$pdf->SetFillColor(255, 248, 225);
			$pdf->SetFont('Arial', '', 10);
			$pdf->SetDrawColor(0, 0, 0);
		
			$i = 1;
			$max = count($data);
			foreach($data as $product_id => $product_data){
				if($i == 1){
					$border_col_1 = "LTR";
					$border_col_2 = "RT";
				}else if($i == $max){
					$border_col_1 = "LRB";
					$border_col_2 = "RB";
				}else{
					$border_col_1 = "LR";
					$border_col_2 = "R";
				}
				$pdf->Cell($this->col_1_width, 
						   $this->content_line_height, 
						   nrvbd_pdf_text($product_data['name']), 
						   $border_col_1, 
						   0, 
						   'L');
				$pdf->Cell($this->col_2_width, 
						   $this->content_line_height, 
						   nrvbd_pdf_text($product_data['quantity']), 
						   $border_col_2, 
						   1, 
						   'R');
				$i++;	
			}
			
		}


		/**
		 * Make some calculations for the pdf data
		 * @method process_data
		 * @return array
		 */
		private function process_data()
		{
			$table_data = array();
			foreach($this->data as $key => $delivery_data){
				if($delivery_data['type'] == "adresse"){
					$WC_Order = \wc_get_order($delivery_data['id']);
					if($WC_Order instanceof \WC_Order){
						$items = $WC_Order->get_items();					
						foreach($items as $item_id => $item){
							$product = $item->get_product();

							if($product->get_type() == 'brunch'){
								if($product->get_brunch_date() != $this->delivery_date){
									continue;
								}
								if(!isset($table_data[$product->get_id()])){
									$table_data[$product->get_id()] = array("addons" => array(),
																			"quantity" => 0,
																			"name" => $product->get_name());
								}

								$addons = $item->get_all_formatted_meta_data('');
								foreach($addons as $k => $addon){												
									if($addon->display_key == '_reduced_stock'){
										continue;
									}
									$the_key = trim(strip_tags($addon->display_key));
									$the_value = trim(strip_tags($addon->display_value));			
									if(!isset($table_data[$product->get_id()]["addons"][$the_key])){
										$table_data[$product->get_id()]["addons"] += array(
											$the_key => array($the_value => 0)
										);
									}
									if(!isset($table_data[$product->get_id()]["addons"][$the_key][$the_value])){
										$table_data[$product->get_id()]["addons"][$the_key] += array($the_value => 0);
									}
									$table_data[$product->get_id()]["addons"][$the_key][$the_value] ++;
								}
								$table_data[$product->get_id()]["quantity"] += $item->get_quantity();
							}else{
								if(!isset($table_data["extra"])){
									$table_data["extra"] = array();
								}
								if(!isset($table_data["extra"][$product->get_id()])){
									$table_data["extra"] += array($product->get_id() => 
																	array("name" => $product->get_name(),
																		"quantity" => 0));
								}
								$table_data["extra"][$product->get_id()]['quantity'] += $item->get_quantity();
							}	
						}
					}
				}
			}
			
			if(isset($table_data["extra"])){
				$extra = $table_data["extra"];
				unset($table_data["extra"]);
				$table_data['extra'] = $extra;
			}
			return $table_data;
		}
    }
}
