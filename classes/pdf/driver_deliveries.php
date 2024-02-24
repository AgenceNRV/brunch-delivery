<?php

namespace nrvbd\pdf;

use nrvbd\helpers;

if (!class_exists('\nrvbd\pdf\driver_deliveries')) {
    class driver_deliveries
    {

		public $font_family = 'Arial';

		public $data;

		public $delivery_date;

		private $col_1_width;

		private $col_2_width;

		private $line_height = 10;

		private $content_line_height = 7;

		private $reverse_data = false;

		private $selected_driver = null;

		public function __construct(string $delivery_date, 
								    array $data = array(), 
									bool $reverse_data = false)
		{
			$this->data = $data;
			$this->delivery_date = $delivery_date;
			$this->reverse_data = $reverse_data;
		}


		public function select_driver(int $driver)
		{
			$this->selected_driver = $driver;
		}


        public function save(string $name, 
							 string $output = 'I')
        {
            error_reporting(0);
            $pdf = new \FPDF();

            $remaining_height = $pdf->GetPageHeight() - $pdf->GetY(); 	
			
			$this->col_1_width = ($pdf->GetPageWidth() - 20) * 0.6;
			$this->col_2_width = ($pdf->GetPageWidth() - 20) * 0.4;

            foreach($this->data as $key => $driver_delivery_data){
				// if($this->selected_driver !== null 
				// 	&& $this->selected_driver != $driver_delivery_data['driver']){
				// 	continue;
				// }

				$driver = new \nrvbd\entities\driver($driver_delivery_data['driver'] ?? null);
				$driver_name = __("Unknown driver", "nrvbd");
				if($driver->db_exists()){
					$driver_name = $driver->lastname . " " . $driver->firstname;
				}
				$pdf->AddPage();
				$this->generate_header($pdf, $driver_name);
				if(!empty($driver_delivery_data['adresses'])){
					$addresses = $driver_delivery_data['adresses'];
					$table_data = $this->process_table_data($addresses);
				}

				if($this->reverse_data){
					$table_data = array_reverse($table_data);
				}

				foreach($table_data as $order){
					$address_data = $order['address'];
					$customer = $address_data['last_name'] . " " . $address_data['first_name'];
					$company = $address_data['company'] ?? "";
					$phone = $address_data['phone'] ?? "";
					
					$raw_address = $customer . "\n";
					if($company != ""){
						$raw_address .= "Société : " . $company . "\n";
					}
					
					$raw_address .= $address_data['address_1'] . "\n";
					if($address_data['address_2'] != ""){
						$raw_address .= $address_data['address_2'] . "\n";
					}
					$raw_address .= $address_data['postcode']  . " " . $address_data['city'];
					
					if($phone != ""){
						$raw_address .= "\n Téléphone : " . $phone;
					}
					if($order['note'] != ""){
						$raw_address .= "\n Note :\n " . $order['note'];
					}

					$products_data = $order['products'] ?? array();

					$extra_data = $order['extra'] ?? array();
					$i = 0;
					$products_count = count($products_data);
					foreach($products_data as $product){
						$product_name = $product['name'] . " x" . $product['quantity'];
						$person_1 = $this->convert_addons_to_string($product['addons'], 1);
						$person_2 = $this->convert_addons_to_string($product['addons'], 2);
						if($i == 0){
							$extra_string = $this->convert_extra_to_string($extra_data);
						}else{
							$extra_string = "";
						}

						// Prepare sizes
						$sizes = $this->calculate_table_sizes($product_name,
							                                  $raw_address, 
															  $person_1,
															  $person_2,
															  $extra_string);		
															  
						// Add page if necessary
						if(($remaining_height - $sizes['table_height'] - 30) < 0){
							$pdf->AddPage();
							$remaining_height = $pdf->GetPageHeight() - $pdf->GetY(); 
						}
				
						// Si pb de superposition de tableaux si ce n'est pas lié à l'ajout de cet header
						// if($products_count == 1 || ($products_count > 1 && $i == 0)){
						$this->generate_table_header($pdf,
														$customer,
														$order['id']);
						// }

						// Generate table
						$this->generate_table($pdf, 
											  $pdf->getY(),
											  $sizes,
											  $product_name, 
											  $person_1, 
											  $person_2, 
											  $raw_address, 
											  $extra_string);

						// Update remaining height
						$remaining_height = $pdf->GetPageHeight() - $pdf->GetY();

						$i++;
					}		
					$pdf->setY($pdf->getY() + $this->line_height);
				}
            }
			// die();
            return $pdf->Output($output, $name);
        }


		/**
		 * Generate the header
		 * @method generate_header
		 * @param  fpdf   $pdf
		 * @param  string $driver_name
		 * @return void
		 */
		private function generate_header(&$pdf,
										 string $driver_name)
		{
			$pdf->SetFont('Arial', 'B', 16);
			$pdf->SetFillColor(90, 58, 34);
			$pdf->SetDrawColor(0, 0, 0);
			$pdf->SetTextColor(255,255,255);
			$pdf->Rect(10, 10, $pdf->GetPageWidth() - 20, 20, 'D');
			$pdf->SetXY(10, 10);
			$pdf->MultiCell($pdf->GetPageWidth() - 20, 20, nrvbd_pdf_text("Livraisons du " . $this->delivery_date . " pour " . $driver_name), '', 'C', true);
			$pdf->ln(10);
		}


		/**
		 * Generate the table header with customer and order number
		 * @method generate_table_header
		 * @param  fpdf $pdf
		 * @param  string $customer
		 * @param  string $order
		 * @return float
		 */
		private function generate_table_header(&$pdf, 
											   string $customer, 
											   string $order)
		{
			$pdf->SetFont('Arial', '', 8);
			$pdf->SetFillColor(238, 211, 189);
      		$pdf->SetTextColor(0,0,0);
			$header_y = $pdf->GetY();
			$pdf->SetDrawColor(0, 0, 0);
			$pdf->Rect(10, $header_y, $this->col_1_width, $this->line_height, 'FD');
		
			$pdf->SetXY(10, $header_y);
			$pdf->SetFont('Arial', 'B', 10);
			$pdf->MultiCell($this->col_1_width, $this->line_height, nrvbd_pdf_text($customer), '', 'C', true);
			$pdf->SetXY(10 + $this->col_1_width, $header_y);
			$pdf->SetDrawColor(0, 0, 0);
			$pdf->Rect(10 + $this->col_1_width, $header_y, $this->col_2_width, $this->line_height, 'D');		
			$pdf->MultiCell($this->col_2_width, $this->line_height, "#{$order}", '', 'C', true);
			return $pdf->GetY();
		}


		/**
		 * Generate the product table
		 * @method generate_table
		 * @param  fpdf $pdf
		 * @param  float  $start_y
		 * @param  array  $sizes
		 * @param  string $product_name
		 * @param  string $person_1
		 * @param  string $person_2
		 * @param  string $raw_address
		 * @param  string $extra_string
		 * @return float
		 */
		private function generate_table(&$pdf, 
										float $start_y,
										array $sizes = array(),
										string $product_name = "",
									  	string $person_1 = "", 
									  	string $person_2 = "", 
									   	string $raw_address = "", 
									   	string $extra_string = "")
		{		
			$pdf->setY($start_y);
			$pdf->SetFont('Arial', '', 10);
			
			$pdf->SetDrawColor(0, 0, 0); 	

			// Colonne 1
			$y = $pdf->getY() + 10;
			$pdf->SetDrawColor(0, 0, 0);
			$pdf->SetFillColor(180, 148, 90);
			$pdf->Rect(10, $start_y, $this->col_1_width, $this->line_height, 'FD');
			$pdf->SetXY(10, $start_y);
			$pdf->MultiCell($this->col_1_width, 
							$this->content_line_height+3,
							nrvbd_pdf_text($product_name), 
							'', 
							'C', true);

			$pdf->SetXY(10, $y);
			$pdf->Rect(10, $y, $this->col_1_width / 2, $sizes['column_1'], 'D');			
			$pdf->SetFont('Arial', 'B', 10);			
			$pdf->MultiCell($this->col_1_width / 2, 
							$this->content_line_height, 
							nrvbd_pdf_text('Personne 1'), 
							'', 
							'C');					   
			$pdf->SetFont('Arial', '', 8);
			$pdf->MultiCell($this->col_1_width / 2, 
						    5, 
							nrvbd_pdf_text($person_1), 
							'',
							'L');
		
			$pdf->SetXY(10 + $this->col_1_width / 2, $y);
			$pdf->Rect(10 + $this->col_1_width / 2, $y, $this->col_1_width / 2, $sizes['column_1'], 'D'); 		
			$pdf->SetFont('Arial', 'B', 10);			
			$pdf->MultiCell($this->col_1_width / 2, 
							$this->content_line_height, 
							nrvbd_pdf_text('Personne 2'), 
							'', 
							'C');		
							
			$pdf->SetXY(10 + $this->col_1_width / 2, $y + $this->content_line_height);				   
			$pdf->SetFont('Arial', '', 8);
			$pdf->MultiCell($this->col_1_width / 2, 
							5, 
							nrvbd_pdf_text($person_2), 
							'',
							'L');
		
			// Colonne 2
			$pdf->SetXY(10 + $this->col_1_width, $start_y);
      		$pdf->SetFillColor(202, 202, 255);		   
			$pdf->SetFont('Arial', '', 10);	
			$pdf->Rect(10 + $this->col_1_width, $start_y, $this->col_2_width, $sizes['column_2_1'], 'FD');
			$pdf->MultiCell($this->col_2_width,
						    $this->content_line_height,
							nrvbd_pdf_text($raw_address), 
							'', 
							'C', true);
			$pdf->ln($this->line_height);
   
			$pdf->SetFont('Arial', '', 8);	
			$pdf->SetXY(10 + $this->col_1_width, $pdf->GetY());
			$pdf->Rect(10 + $this->col_1_width, $pdf->GetY(), $this->col_2_width, $sizes['column_2_2'], 'D');
			$pdf->MultiCell($this->col_2_width, 
							5, 
							nrvbd_pdf_text($extra_string), 
							'', 
							'C');

			$pdf->setY($start_y + $sizes['table_height'] - $this->content_line_height);
			return $pdf->GetY();
		}


		/**
		 * Calculate the sizes
		 * @method calculate_table_sizes
		 * @param  string $product_name
		 * @param  string $raw_address
		 * @param  string $content_column_1_1
		 * @param  string $content_column_1_2
		 * @param  string $extra_string
		 * @return array
		 */
		private function calculate_table_sizes(string $product_name = "",
											   string $raw_address = "", 
											   string $content_column_1_1 = "",
											   string $content_column_1_2 = "",
											   string $extra_string = "")
		{
			$sizes = array(
				"product_name" => 0,
				"column_1" => 0,
				"column_2_1" => 0,
				"column_2_2" => 0,
				"table_height" => 0
			);

			$product_name_height = $this->simulate_multi_cell_height($this->col_1_width, 
																	 $this->content_line_height, 
																	 nrvbd_pdf_text($product_name));		
			$content_1_1_height = $this->simulate_multi_cell_height($this->col_1_width / 2, 
																	5, 
																	nrvbd_pdf_text($content_column_1_1));
			$content_1_2_height = $this->simulate_multi_cell_height($this->col_1_width / 2, 
																	5, 
																	nrvbd_pdf_text($content_column_1_2));
			$content_2_1_height = $this->simulate_multi_cell_height($this->col_2_width, 
																	$this->content_line_height, 
																	nrvbd_pdf_text($raw_address));
			$content_2_2_height = $this->simulate_multi_cell_height($this->col_2_width, 
																	5, 
																	nrvbd_pdf_text($extra_string));

			$column_2_height = $content_2_1_height + $content_2_2_height;
			$column_1_height = max($content_1_1_height, $content_1_2_height) + $product_name_height;

			$table_height = max($column_1_height, $column_2_height);
			if($column_1_height > $column_2_height){
				$hcol1 = $column_1_height - $product_name_height;
				$hcol21 = $content_2_1_height;
				$hcol22 = $column_1_height - $hcol21 - $this->content_line_height;
			}elseif($column_1_height < $column_2_height){
				$hcol1 = $column_2_height;
				$hcol21 = $content_2_1_height;
				$hcol22 = $content_2_2_height + $product_name_height;
			}
			$sizes['product_name'] = $product_name_height;
			$sizes['column_1'] = $hcol1;
			$sizes['column_2_1'] = $hcol21;
			$sizes['column_2_2'] = $hcol22;
			$sizes['table_height'] = $table_height;

			return $sizes;
		}


		/**
		 * Simulate the multi cell height
		 * @method simulate_multi_cell_height
		 * @param  float $width
		 * @param  float $height
		 * @param  string $text
		 * @return int
		 */
        private function simulate_multi_cell_height($width, $height, $text)
        {
            $tempPdf = new \FPDF();
            $tempPdf->AddPage();
            $tempPdf->SetFont($this->font_family, "B", 10);
            $tempPdf->MultiCell($width, $height, $text);
			return $tempPdf->getY();
        }


		/**
		 * Make some calculations for the table data
		 * @method process_table_data
		 * @param  array $addresses
		 * @return array
		 */
		private function process_table_data(array $addresses)
		{
			$table_data = array();
			foreach($addresses as $k => $address){
				$order_data = array();
				$WC_Order = \wc_get_order($address['adresse']);
				if($WC_Order instanceof \WC_Order){
					$order_data['id'] = $WC_Order->get_id();
					$order_data['address'] = $WC_Order->get_address('shipping');
					$order_data['note'] = $WC_Order->get_customer_note();
					$items = $WC_Order->get_items();					
					foreach ($items as $item_id => $item){
						$order_data_item_data = array();
						$product = $item->get_product();
						// if($product->get_brunch_date() != $this->delivery_date){
						// 	continue;
						// }
						$order_data_item_data['name'] = $product->get_name();
						$order_data_item_data['quantity'] = $item->get_quantity();
						if($product->get_type() == 'brunch'){
							$addons = $item->get_all_formatted_meta_data( '' );
							$person = 0;
							$first_array_key = array_key_first($addons);
							if($addons[$first_array_key]->display_key == '_reduced_stock'){
								unset($addons[$first_array_key]);
								$first_array_key = array_key_first($addons);
							}
							$first_key = $addons[$first_array_key]->display_key;
							$kept_addons = array();
							foreach($addons as $k => $addon){
								if($first_key == $addon->display_key){
									$person++;
								}
								if(!isset($kept_addons[$person]) || !is_array($kept_addons[$person])){
									$kept_addons[$person] = array();
								}
								if(strpos($addon->display_key, '_') === 0){
									continue;
								}
								$kept_addons[$person][$addon->display_key] = $addon->display_value;
							}
							$order_data_item_data['addons'] = $kept_addons;
							$order_data['products'][] = $order_data_item_data;
						}else{
							$order_data['extra'][] = $order_data_item_data;
						}
					}
				}
				$table_data[] = $order_data;
			}
			return $table_data;
		}


		/**
		 * Convert addons to string
		 * @method convert_addons_to_string
		 * @param  array   $addons
		 * @param  integer $part
		 * @return string
		 */
		private function convert_addons_to_string(array $addons, int $part)
		{
			$string = "";
			if(isset($addons[$part]) && is_array($addons[$part])){
				$s = count($addons[$part]);
				$i = 0;
				foreach($addons[$part] as $key => $value){
					$string .= $value;
					$i++;
					if($i < $s){
						$string .= "\n";
					}
				}
			}
			return $string;
		}


		/**
		 * Convert extra data to string
		 * @method convert_extra_to_string
		 * @param  array $extra_data
		 * @return string
		 */
		private function convert_extra_to_string(array $extra_data)
		{
			$string = "";
			if(is_array($extra_data)){
				$s = count($extra_data);
				$i = 0;
				foreach($extra_data as $key => $value){
					$string .= $value['name'] . " x" . $value['quantity'];
					$i++;
					if($i < $s){
						$string .= ", ";
					}
				}
			}
			return $string;
		}
    }
}
