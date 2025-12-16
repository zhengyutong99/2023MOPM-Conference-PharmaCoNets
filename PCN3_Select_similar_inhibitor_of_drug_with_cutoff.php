<?php
############################
#Aim: Select_similar_inhibitor_of_drug_with_cutoff
#Usage:time php PCN3_Select_similar_inhibitor_of_drug_with_cutoff.php O_NP1_cm_ac_combined_feas_small_set_drugbank_tanimoto.txt 0.8
#Input_1: O_NP1_cm_ac_combined_feas_small_set_drugbank_tanimoto.txt
#Output_1: Tanimoto similarity between inhibitors and drugs
############################

    ini_set("memory_limit", -1);
    Error_Reporting(-1);

    //$input = "drugbank_approved_SARS_DEGs_new_set_add_BindingDB_2020m10_filtered_in_10um_ac_cm_tanimotos.txt";
	$input = "./input/".$argv[1];
	$cutoff = $argv[2]; //0.8
    #$input = "Text_mining_drug_158drugbank_approved_20200702_cm_ac_tanimoto.txt";
    #$output = "DrugBank_2303_SARS_DEGs_new_add_inhibitors_BDBM_similarity_80.txt";
    $output = "./output/O_PCN3_".substr($argv[1], 7, -4)."_similarity_".$cutoff.".txt";
    $pcn4_input = "./input/I_PCN4_".substr($argv[1], 7, -4)."_similarity_".$cutoff.".txt";

    $output_string = "";

    $Lines = File($input);
    $BDBM_mapping = Explode("\t", $Lines[0]);
    $BDBM_mapping = Array_Map("Trim", $BDBM_mapping);
    unset ($Lines[0]);

    foreach ($Lines as $line)
    {
        $Pieces = Explode("\t", $line);
        $Pieces = Array_Map("Trim", $Pieces);
        $db_id = $Pieces[0];
        unset ($Pieces[0]);
        foreach ($Pieces as $index => $sim)
        {
            if ($sim < $cutoff) continue;
            $output_string .= "{$db_id}\t{$BDBM_mapping[$index]}\t{$sim}\n";
        }
    }
    File_Put_Contents($output, $output_string, LOCK_EX);
    File_Put_Contents($pcn4_input, $output_string, LOCK_EX);

?>
