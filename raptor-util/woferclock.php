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

	if ($argc < 2) {
		printf("USAGE: php woferclock.php <nest bucket ID> <frequency_multiplier> <input file> <output file>\n");
		exit(1);
	}

	$p9_allowed_bucket_nest_mhz = array(0, 1600, 1866, 2000, 2133, 2400, 2666);

	$nest_bucket_id = $argv[1];
	$frequency_offset_ratio = $argv[2];
	$input_file = $argv[3];
	$output_file = $argv[4];

	if (($nest_bucket_id < 1) || ($nest_bucket_id > 6)) {
		printf("[ERROR] Invalid nest bucket ID specified\n");
		exit(1);
	}

	$nest_mhz = $p9_allowed_bucket_nest_mhz[$nest_bucket_id];
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
		printf("[ERROR] Unable to open input file for read!\n");
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
				$next_wofline[7] = round($next_wofline[7] * $frequency_offset_ratio);
				$next_wofline[8] = round($next_wofline[8] * $frequency_offset_ratio);
				$next_wofline[9] = round($nest_mhz);
				$next_wofline[23] = round($next_wofline[23] * $frequency_offset_ratio);
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