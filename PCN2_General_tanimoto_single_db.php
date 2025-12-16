<?php
############################
#Aim: Select_similar_inhibitor_of_drug_with_cutoff
#Usage:time time php PCN3_Select_similar_inhibitor_of_drug_with_cutoff.php O_NP1_cm_ac_combined_feas_small_set_drugbank_tanimoto.txt 0.5 
#Input_1: O_NP1_WB_BindingDB_20222m5_filtered_in_10um_cm_ac_combined_feas.txt //Output of PCN2
#Output_1: Tanimoto similarity between inhibitors and drugs
############################

    ini_set("memory_limit", -1);
    Error_Reporting(-1);

    //$query_fea = "Huang_ap_fea.txt";
    $query_fea = "./input/".$argv[1]; //Input_1: Output of PCN1
    #$query_fea = "SARS_DEGs_new_set_add_BindingDB_2020m10_filtered_in_10um_ac_cm_combined_feas_p4.txt";
    $database_fea = "/data/240_data/zhengyutong/DrugBank202401/DrugBank_approved_mol_202401_cm_ac_features.txt";
    //$database_fea = "Huang_ap_fea.txt";
    //$output = "drugbank_approved_HMDB_metabolites_all2all_checkmol_ac_tanimotos_test.txt";
    //$output = "Huang_ap_fea_all2all_tanimoto_similarity.txt";
    $output = "./output/O_PCN2_".substr($argv[1], 7, -4)."_drugbank_tanimoto.txt";
    $pcn3_input = "./input/I_PCN3_".substr($argv[1], 7, -4)."_drugbank_tanimoto.txt";
    $DB_feas = Array();
    $Output_header = Array();
    $Output_string = Array();

    Function General_tanimoto ($fea_1, $fea_2)
    {
        $Features_1 = Explode("\t", $fea_1);
        $Features_1 = Array_Map("Trim", $Features_1);
        unset ($Features_1[0]);
        $Features_2 = Explode("\t", $fea_2);
        $Features_2 = Array_Map("Trim", $Features_2);
        unset ($Features_2[0]);
        $union = 0;
        $intersection = 0;
        foreach ($Features_1 as $index => $feature_1)
        {
            $union += Max($feature_1, $Features_2[$index]);
            $intersection += Min($feature_1, $Features_2[$index]);
        }
        $tanimoto = Round($intersection / $union, 3);

        return $tanimoto;
    }

    $DB_fea_lines = File($database_fea);
    unset ($DB_fea_lines[0]);
    foreach ($DB_fea_lines as $db_fea_line)
    {
        $Pieces = Explode("\t", $db_fea_line);
        $db_id = Trim($Pieces[0]);
        $DB_feas[$db_id] = $db_fea_line;
    }
    //Print_r($DB_feas);

    $Lines = File($query_fea);
    unset ($Lines[0]);
    foreach ($Lines as $line)
    {
        $Pieces = Explode("\t", $line);
        $query_id = Trim($Pieces[0]);
        Array_Push($Output_header, $query_id);
        $Tanimotos_fea_rank = Array();
        foreach ($DB_feas as $db_id => $db_fea_line)
        {
            $tani = General_tanimoto($line, $db_fea_line);
            if (!Array_Key_Exists($db_id, $Output_string))
            {
                $Output_string[$db_id] = $tani;
            }
            else
            {
                $Output_string[$db_id] .= "\t" . $tani;
            }
        }
    }
    $output_string = "Drugs\t" . Implode("\t", $Output_header) . "\n";
    foreach ($Output_string as $db_id => $string)
    {
        $output_string .= "{$db_id}\t{$string}\n";
    }
    File_Put_Contents($output, $output_string, LOCK_EX);
    File_Put_Contents($pcn3_input, $output_string, LOCK_EX);

?>
