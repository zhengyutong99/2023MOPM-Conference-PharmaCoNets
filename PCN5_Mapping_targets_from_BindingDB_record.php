<?php
############################
#Aim: Mapping_targets_from_BindingDB_record
#Usage:time php PCN4_Mapping_targets_from_BindingDB_record.php O_PCN3O_NP1_cm_ac_combined_feas_small_set_drugbank_tanimoto_tanimoto_similarity_0.5.txt O_NP1_WB_BindingDB_20222m5_filtered_in_10um.tsv drugbank_small_set
#Input_1: Output of drug&inihbitor similarity above cut off //output of PCN3
#Input_2: BindingDB records with selected column //output of PCN0
#Input_3: File name of outputs
#Output_1: {File name of outputs}_with_target_affinity.txt
#Output_2: {File name of outputs}_with_target_affinity_target_line.txt
############################

    ini_set("memory_limit", -1);
    Error_Reporting(-1);

    $input_list = "./input/".$argv[1];
    $input = "./output/".$argv[2];
	// $output_name = $argv[3];
    $output_name = "./output/O_PCN5_drugbank_small_set";
    $output = $output_name."_with_target_affinity.txt";
    $output_2 = $output_name."_with_target_affinity_target_line.txt";
    $pcn7_input = "./input/I_PCN7_drugbank_small_set_with_target_affinity_target_line.txt";

    $BDBM_ids = Array();
    $Targets = Array();
    $IC50 = Array();
    $KI = Array();
    $KD = Array();
    $Pairs = Array();

    $Lines = File($input_list);
    foreach ($Lines as $line)
    {
        $Pieces = Explode("\t", $line);
        $Pieces = Array_Map("Trim", $Pieces);
        $bdbm_id = $Pieces[1];
        Array_Push($BDBM_ids, $bdbm_id);
    }
    $BDBM_ids = Array_Unique($BDBM_ids);

    $Lines = File($input);
    unset ($Lines[0]);
    foreach ($Lines as $line)
    {
        $Pieces = Explode("\t", $line);
        $Pieces = Array_Map("Trim", $Pieces);
        $bdbm_id = "BDBM" . Str_Pad($Pieces[4], 8, "0", STR_PAD_LEFT);
        if (!In_Array($bdbm_id, $BDBM_ids))   continue;
        $target = $Pieces[25];
        $ki = $Pieces[8];
        $ic50 = $Pieces[9];
        $kd = $Pieces[10];
        if (!Array_Key_Exists($bdbm_id, $Targets))
        {
            $Targets[$bdbm_id] = Array();
        }
        Array_Push($Targets[$bdbm_id], $target);
        $pair = "{$bdbm_id}_{$target}";
        if ($ic50 != "")
        {
            if (!Array_Key_Exists($pair, $IC50))
            {
                $IC50[$pair] = $ic50;
                if (!Array_Key_Exists($bdbm_id, $Pairs))
                {
                    $Pairs[$bdbm_id] = Array();
                }
                Array_Push($Pairs[$bdbm_id], $pair);
            }
            else
            {
                $first_c_new = SubStr($ic50, 0, 1);
                $first_c_old = SubStr($IC50[$pair], 0, 1);
                if (($first_c_new == "<") && ($first_c_old != "<")) continue;
                elseif (($first_c_new != "<") && ($first_c_old == "<"))
                {
                    $IC50[$pair] = $ic50;
                }
                elseif (($first_c_new == "<") && ($first_c_old == "<"))
                {
                    $value_new = (float)SubStr($ic50, 1);
                    $value_old = (float)SubStr($IC50[$pair], 1);
                    if ($value_new < $value_old)
                    {
                        $IC50[$pair] = $ic50;
                    }
                }
                elseif (($first_c_new != "<") && ($first_c_old != "<"))
                {
                    if ($ic50 < $IC50[$pair])
                    {
                        $IC50[$pair] = $ic50;
                    }
                }
            }
        }

        if ($ki != "")
        {
            if (!Array_Key_Exists($pair, $KI))
            {
                $KI[$pair] = $ki;
                if (!Array_Key_Exists($bdbm_id, $Pairs))
                {
                    $Pairs[$bdbm_id] = Array();
                }
                Array_Push($Pairs[$bdbm_id], $pair);
            }
            else
            {
                $first_c_new = SubStr($ki, 0, 1);
                $first_c_old = SubStr($KI[$pair], 0, 1);
                if (($first_c_new == "<") && ($first_c_old != "<")) continue;
                elseif (($first_c_new != "<") && ($first_c_old == "<"))
                {
                    $KI[$pair] = $ki;
                }
                elseif (($first_c_new == "<") && ($first_c_old == "<"))
                {
                    $value_new = (float)SubStr($ki, 1);
                    $value_old = (float)SubStr($KI[$pair], 1);
                    if ($value_new < $value_old)
                    {
                        $KI[$pair] = $ki;
                    }
                }
                elseif (($first_c_new != "<") && ($first_c_old != "<"))
                {
                    if ($ki < $KI[$pair])
                    {
                        $KI[$pair] = $ki;
                    }
                }
            }
        }

        if ($kd != "")
        {
            if (!Array_Key_Exists($pair, $KD))
            {
                $KD[$pair] = $kd;
                if (!Array_Key_Exists($bdbm_id, $Pairs))
                {
                    $Pairs[$bdbm_id] = Array();
                }
                Array_Push($Pairs[$bdbm_id], $pair);
            }
            else
            {
                $first_c_new = SubStr($kd, 0, 1);
                $first_c_old = SubStr($KD[$pair], 0, 1);
                if (($first_c_new == "<") && ($first_c_old != "<")) continue;
                elseif (($first_c_new != "<") && ($first_c_old == "<"))
                {
                    $KD[$pair] = $kd;
                }
                elseif (($first_c_new == "<") && ($first_c_old == "<"))
                {
                    $value_new = (float)SubStr($kd, 1);
                    $value_old = (float)SubStr($KD[$pair], 1);
                    if ($value_new < $value_old)
                    {
                        $KD[$pair] = $kd;
                    }
                }
                elseif (($first_c_new != "<") && ($first_c_old != "<"))
                {
                    if ($kd < $KD[$pair])
                    {
                        $KD[$pair] = $kd;
                    }
                }
            }
        }
    }

    $U_Pairs = Array();
    foreach ($Pairs as $bdbm_id => $R_Pairs)
    {
        $U_Pairs[$bdbm_id] = Array();
        $U_Pairs[$bdbm_id] = Array_Unique($R_Pairs);
    }
    //Print_r($U_Pairs);

    $output_string = "";
    $output_string_2 = "Drug\tBDBM\tSimilarity\tTarget\tIC50\tKi\tKd\n";
    $Lines = File($input_list);
    foreach ($Lines as $line)
    {
        $Pieces = Explode("\t", $line);
        $Pieces = Array_Map("Trim", $Pieces);
        $bdbm_id = $Pieces[1];
        @$Targets[$bdbm_id] = Array_Unique($Targets[$bdbm_id]);
        $output_string .= Trim($line) . "\t" . Count($Targets[$bdbm_id]);
        foreach ($U_Pairs[$bdbm_id] as $pair)
        {
            if (Array_Key_Exists($pair, $IC50))
            {
                $Pieces_2 = Explode("_", $pair);
                $target = Trim($Pieces_2[1]);
                $output_string .= "\t{$target}\tIC50:{$IC50[$pair]}";
            }
            if (Array_Key_Exists($pair, $KI))
            {
                $Pieces_2 = Explode("_", $pair);
                $target = Trim($Pieces_2[1]);
                $output_string .= "\t{$target}\tKi:{$KI[$pair]}";
            }
            if (Array_Key_Exists($pair, $KD))
            {
                $Pieces_2 = Explode("_", $pair);
                $target = Trim($Pieces_2[1]);
                $output_string .= "\t{$target}\tKd:{$KD[$pair]}";
            }
        }
        $output_string .= "\n";
        foreach ($Targets[$bdbm_id] as $target)
        {
            $output_string_2 .= "{$Pieces[0]}\t{$Pieces[1]}\t{$Pieces[2]}\t{$target}";
            $pair = "{$bdbm_id}_{$target}";
            if (Array_Key_Exists($pair, $IC50))
            {
                $output_string_2 .= "\t{$IC50[$pair]}";
            }
            else
            {
                $output_string_2 .= "\t-";
            }
            if (Array_Key_Exists($pair, $KI))
            {
                $output_string_2 .= "\t{$KI[$pair]}";
            }
            else
            {
                $output_string_2 .= "\t-";
            }
            if (Array_Key_Exists($pair, $KD))
            {
                $output_string_2 .= "\t{$KD[$pair]}";
            }
            else
            {
                $output_string_2 .= "\t-";
            }
            $output_string_2 .= "\n";
        }
    }
    File_Put_Contents($output, $output_string, LOCK_EX);
    File_Put_Contents($output_2, $output_string_2, LOCK_EX);
    File_Put_Contents($pcn7_input, $output_string_2, LOCK_EX);

?>
