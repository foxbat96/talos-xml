<?php
	// (c) 2018 Raptor Engineering, LLC
	// Released under the terms of the AGPL v3
	//
	// WARNING: Use at own risk!  May cause permanent processor and/or mainboard damage.
	//
	// After update:
	//
	// op-build talos_defconfig
	// op-build machine-xml-dirclean machine-xml-rebuild
	// op-build openpower-pnor-rebuild

	// 22 core
	// $frequency_offset_percent = 15;
	$frequency_offset_percent = 10;

	$input_file = 'WOF_V7_3_3_SFORZA_22_190_2750_TM.csv';
	$output_file = 'WOF_V7_3_3_SFORZA_22_190_2750_TM.csv';

// 	// 18 core
// 	// $frequency_offset_percent = 15;	// 240W
// 	$frequency_offset_percent = 10;		// 210W
// 
// 	$input_file = 'WOF_V7_3_3_SFORZA_18_190_2800_TM.csv';
// 	$output_file = 'WOF_V7_3_3_SFORZA_18_190_2800_TM.csv';

	$wof_data_in = array();
	$handle = fopen($input_file, "r");
	if(empty($handle) === FALSE) {
		printf("Loading initial table data\n");
		while(($data = fgetcsv($handle, 10000, ",")) !== FALSE){
			$wof_data_in[] = $data;
		}
		fclose($handle);
	}
	else {
		printf("[ERROR] Unable to open input file for read!");
		exit(0);
	}

	$wof_data_in_count = count($wof_data_in);
	$wof_data_out = $wof_data_in;
	// print_r($wof_data_out);

	// Get maximum frequency
	$maximum_frequency = $wof_data_in[1][23];

	$fsync_counter = 0;
	$line_number = 0;
	$handle = fopen($output_file, "w");
	if(empty($handle) === FALSE) {
		foreach($wof_data_out as &$wofline) {
			$next_wofline = $wofline;
			$ceff = $next_wofline[14];
			if ($line_number > 0) {
				$next_wofline[23] = round($next_wofline[23] + (($next_wofline[23] * $frequency_offset_percent) / 100));
				if ($next_wofline[23] > $maximum_frequency) {
					$next_wofline[23] = $maximum_frequency;
				}
			}

			fputcsv($handle, $next_wofline, ",", '"', "\\");
			$fsync_counter++;
			if ($fsync_counter > 1000) {
				echo ".";
				fflush($handle);
				$fsync_counter = 0;
			}
			$line_number++;
		}

		printf("\nDone!\n");
	}

	else {
		printf("[ERROR] Unable to open output file for write!\n");
	}
?>