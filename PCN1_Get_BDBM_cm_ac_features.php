<?php
############################
#Aim: Get BDBM cm ac features
#Usage:time php PCN1_Get_BDBM_cm_ac_features.php O_I_BRCA_DEG_sets_1332_filtered_in_10um.tsv 
//real    80m37.056s 有error
#Input_1: O_I_BRCA_DEG_sets_1332_filtered_in_10um.tsv ("<"10 uM of IC50, EC50...) Output of PCN0
#Output_1: inhibitor mol list
#Output_2: inhibitor checkmol features
#Output_3: inhibitor atom composition (ac) features
#Output_4: inhibitor checkmol+atom composition (ac) features
############################

    ini_set("memory_limit", -1);
    Error_Reporting(-1);

    $input = "./input/".$argv[1]; //Input_1: Output of NP2
    // $input = "./input/I_PCN1_filtered_in_10um.tsv";
    #$input = "NP_Union_WB_BindingDB_2020m10_sel_col_filtered_in_10um.tsv"; //Input_1: Output of NP2
    // $bdbm_mol_list = "/ngs_data/backup/ychsu/5732_HW/Compound_DB/BindingDB_data/BindingDB_mol/list_BindingDB_All_mol_2020m10.txt";
    $bdbm_mol_list = "/data/240_data/zhengyutong/BindingDB202401/list_BindingDB_All_mol_202401.txt";
    @$output_mol_list = "./output/O_PCN1_filtered_in_10um_mol_list.txt";
    #$output_mol_list = "NP_Union_WB_BindingDB_2020m10_sel_col_filtered_in_10um_mol_list.txt";
    @$output_cm = "./output/O_PCN1_filtered_in_10um_checkmol_features.txt";
    #$output_cm = "NP_Union_WB_BindingDB_2020m10_sel_col_filtered_in_10um_cm.txt";
    @$output_ac = "./output/O_PCN1_filtered_in_10um_atom_composition_features.txt";
    #$output_ac = "NP_Union_WB_BindingDB_2020m10_sel_col_filtered_in_10um_ac.txt";
    @$output_cm_ac = "./output/O_PCN1_filtered_in_10um_checkmol_atom_combined_features.txt";
    @$pcn2_input = "./input/I_PCN2_filtered_in_10um_checkmol_atom_combined_features.txt";
    #$output_cm_ac = "NP_Union_WB_BindingDB_2020m10_sel_col_filtered_in_10um_cm_ac_combined_feas.txt";
    $prog_cm = "./dataset_tools/checkmol-latest-linux-x86_64";
    $prog_ac = "./dataset_tools/mod_ac";
    $BDBM_ids = Array();
    $Fea_ac = Array();

    $Lines = File($input);
    unset ($Lines[0]);
    foreach ($Lines as $line)
    {
        $Pieces = Explode("\t", $line);
        $Pieces = Array_Map("Trim", $Pieces);
        $bdbm_id = "BDBM" . Str_Pad($Pieces[4], 8, "0", STR_PAD_LEFT);
        //echo "{$bdbm_id}\n";
        Array_Push($BDBM_ids, $bdbm_id);
    }
    $BDBM_ids = Array_Unique($BDBM_ids);
    $BDBM_ids = Array_Flip($BDBM_ids);
    unset ($Lines);

    $output_string_mol_list = "";
    $Lines = File($bdbm_mol_list);
    foreach ($Lines as $line)
    {
        $bdbm_id = BaseName(Trim($line), ".mol");
        //echo "{$bdbm_id}\n";
        if (!Array_Key_Exists($bdbm_id, $BDBM_ids))   continue;
			// $line = str_replace("/data/", "/ngs_data/backup/", $line);############
			$output_string_mol_list .= $line;
    }
    File_Put_Contents($output_mol_list, $output_string_mol_list, LOCK_EX);
####################################################################################
    $Mol_files = File($output_mol_list);
    $Mol_files = Array_Map("Trim", $Mol_files);

    $output_string_cm = "BindingDB_id";
    foreach (Range(1, 204) as $index)
    {
        $key = "#" . Str_Pad($index, 3, "0", STR_PAD_LEFT);
        $output_string_cm .= "\t$key";
    }
    $output_string_cm .= "\n";
	
    foreach ($Mol_files as $mol_file)
    {
        
		$db_id = SubStr(BaseName($mol_file), 0, -4);
		
        $Checkmol_counts = Array();
        foreach (Range(1, 204) as $index)
        {
            $key = "#" . Str_Pad($index, 3, "0", STR_PAD_LEFT);
            $Checkmol_counts[$key] = 0;
        }

        $Output_cm = Array();
        $cmd = "{$prog_cm} -p {$mol_file}";
		#echo $cmd."\n";
        Exec($cmd, $Output_cm);
		#print_r($Output_cm);
        foreach ($Output_cm as $output_cm_line)
        {
            $Pieces = Explode(":", $output_cm_line);
            $Pieces = Array_Map("Trim", $Pieces);
            $cm_id = $Pieces[0];
            @$cm_count = $Pieces[1];
            $Checkmol_counts[$cm_id] = $cm_count;
        }
        $output_string_cm .= $db_id . "\t" . Implode("\t", $Checkmol_counts) . "\n";

        unset ($Output_cm);
        unset ($Checkmol_counts);
    }
    File_Put_Contents($output_cm, $output_string_cm, LOCK_EX);

    // Extract header line
    $Output_arr = Array();
    $cmd = "{$prog_ac} {$Mol_files[0]}";
    Exec($cmd, $Output_arr);
    $header_line = $Output_arr[0];
    $Pieces = Explode("\t", $header_line);
    $Pieces = Array_Map("Trim", $Pieces);
    $header_line = Implode("\t", $Pieces) . "\n";
    unset ($Output_arr);
    $output_string_ac = $header_line;
    foreach ($Mol_files as $mol_file)
    {
        $Path_parts = PathInfo($mol_file);
        $Output_arr = Array();
        $Pieces = Array();
        $cmd = "{$prog_ac} {$mol_file}";
        Exec($cmd, $Output_arr);
        if (!Array_Key_Exists(1, $Output_arr))  continue;
        $tmp_line = Trim($Output_arr[1]);
        $Pieces = Explode("\t", $tmp_line);
        $Pieces = Array_Map("Trim", $Pieces);
        if (Count($Pieces) !== 11)  continue;
        unset ($Pieces[0]);
        $result_line = $Path_parts['filename'] . "\t" . Implode("\t", $Pieces) . "\n";
        unset ($Pieces);
        unset ($Output_arr);
        $output_string_ac .= $result_line;
    }
    File_Put_Contents($output_ac, $output_string_ac, LOCK_EX);

    $Lines = File($output_ac);
    $Pieces = Explode("\t", $Lines[0]);
    $Pieces = Array_Map("Trim", $Pieces);
    unset ($Pieces[0]);
    $header_line = Implode("\t", $Pieces) . "\n";
    unset ($Lines[0]);
    foreach ($Lines as $line)
    {
        $Pieces = Explode("\t", $line);
        $Pieces = Array_Map("Trim", $Pieces);
        $id = $Pieces[0];
        unset ($Pieces[0]);
        $Fea_ac[$id] = Implode("\t", $Pieces) . "\n";
    }

    $Lines = File($output_cm);
    $header_line = Trim($Lines[0]) . "\t" . $header_line;
    File_put_contents($output_cm_ac, $header_line, LOCK_EX);
    File_put_contents($pcn2_input, $header_line, LOCK_EX);
    unset ($Lines[0]);
    foreach ($Lines as $line)
    {
        $Pieces = Explode("\t", $line);
        $Pieces = Array_Map("Trim", $Pieces);
        $id = $Pieces[0];
        if (!Array_Key_Exists($id, $Fea_ac))    continue;
        $output_string_cm_ac = Trim($line) . "\t" . $Fea_ac[$id];
        File_put_contents($output_cm_ac, $output_string_cm_ac, FILE_APPEND | LOCK_EX);
        File_put_contents($pcn2_input, $output_string_cm_ac, FILE_APPEND | LOCK_EX);
    }

?>
