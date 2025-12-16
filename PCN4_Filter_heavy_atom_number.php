<?php
############################
#Aim: Filter drug’s heavy atoms between 7&125
#Usage:time php PCN4_Filter_heavy_atom_number.php O_PCN3O_NP1_cm_ac_combined_feas_small_set_drugbank_tanimoto_tanimoto_similarity_0.5.txt
#Input_1: Output of drug&inihbitor similarity above cut off //output of PCN3
#Output_1: {File name of outputs}_ha_7_125.txt
############################
    ini_set('memory_limit', '4G');

	$input = "./input/".$argv[1];
	#$input = "O_PCN3O_NP1_cm_ac_combined_feas_small_set_drugbank_tanimoto_tanimoto_similarity_0.5.txt";
    // $input_ha = "/data/71_data/CompoundDB/DrugBank/drugbank_all_mol_20200702_heavy_atom_count.txt";
    $input_ha = "/data/240_data/zhengyutong/DrugBank202401/drugbank_all_mol_202401_heavy_atom_count.txt";
    $output = "./output/O_PCN4_".substr($argv[1], 7, -4)."_ha_7_125.txt";
    $pcn5_input = "./input/I_PCN5_".substr($argv[1], 7, -4)."_ha_7_125.txt";

    $HA = Array();
    $ha_max = 125;
    $ha_min = 7;

    $Lines = File($input_ha);
    foreach ($Lines as $line)
    {
        $Pieces = Explode("\t", $line);
        $Pieces = Array_Map("Trim", $Pieces);
        $db_id = $Pieces[0];
        $ha = $Pieces[2];
        $HA[$db_id] = (int)$ha;
    }

    $output_string = "DrugBank_ID\tBDBM_ID\tCompound_Similarity\tHeavy_Atom\n";
    $Lines = File($input);
    foreach ($Lines as $line)
    {
        $Pieces = Explode("\t", $line);
        $Pieces = Array_Map("Trim", $Pieces);
        $db_id = $Pieces[0];
        $bdbm_id = $Pieces[1];
        $sim = $Pieces[2];
        if (($HA[$db_id] > $ha_max) || ($HA[$db_id] <= $ha_min))    continue;
        $output_string .= "{$db_id}\t{$bdbm_id}\t{$sim}\t{$HA[$db_id]}\n";
    }
    File_Put_Contents($output, $output_string, LOCK_EX);
    File_Put_Contents($pcn5_input, $output_string, LOCK_EX);

?>
