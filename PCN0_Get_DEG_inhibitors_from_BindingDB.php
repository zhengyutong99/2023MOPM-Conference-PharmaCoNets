<?php
############################
#AiM: Get DEG inhibitors from BindingDB database filtered by cutoff
#Usage: time php PCN0_Get_DEG_inhibitors_from_BindingDB.php /data/public/CompoundDB/BindingDB/BindingDB_All_2022m5.tsv I_BRCA_DEG_sets_1332.txt BindingDB_selected_columns.txt 10000.0
#Input_1: BindingDB database #/data/public/CompoundDB/BindingDB/BindingDB_All_2022m5.tsv
#Input_2: DEG list
#Input_3: BindingDB_selected_columns
#Input_4: Cutoff => 10000.0 (10uM)
#Ouput_1: DEG-inhibitor records in BindingDB ("<"10 uM of IC50, EC50...)
#Ouput_2: DEG-inhibitor records in BindingDB (no_value)
#Ouput_3: DEG-inhibitor records in BindingDB (">"10 uM of IC50, EC50...)
############################

    ini_set("memory_limit", -1);
    Error_Reporting(-1);

    $input_BindingDB_data = "/data/240_data/zhengyutong/BindingDB202401/".$argv[1];      // Input_1: BindingDB database
    // $input_BindingDB_data = "/data/71_data/CompoundDB/BindingDB/BindingDB_All_2022m5.tsv";
    $input_DEG_UniP_list = "./input/".$argv[2];         // Input_2: DEG list
    $input_BindingDB_sel_cols = "./input/".$argv[3];    // Input_3: BindingDB selected columns
    $input_cutoff = $argv[4];                           // Input_4: Cutoff => 10000.0 (10uM)

    $output_1 = "./output/O_PCN0_filtered_in_10um.tsv";     //#Ouput_1
    $output_2 = "./output/O_PCN0_filtered_no_value.tsv";    //#Ouput_2
    $output_3 = "./output/O_PCN0_filtered_out_10um.tsv";    //#Ouput_3

    $Sel_cols = File($input_BindingDB_sel_cols);
    $Sel_cols = Array_Map("Trim", $Sel_cols);
    $Sel_col_keys = Array();
    $Tmp_title_lines = Array();
    $UniP_ids = File($input_DEG_UniP_list);
    $UniP_ids = Array_Map("Trim", $UniP_ids);
    // foreach ($UniP_ids as $ids)
    // {
    //     print($ids."\n");
    // }
    $fp_0 = fopen($input_BindingDB_data, "r");
    $string = FGetS($fp_0);
    $Pieces = Explode("\t", $string);
    $Pieces = Array_Map("Trim", $Pieces);
    foreach ($Pieces as $key => $piece)
    {
        if (!In_Array($piece, $Sel_cols))   continue;
        Array_Push($Sel_col_keys, $key);
        Array_Push($Tmp_title_lines, $piece);
    }
    $output_string_1 = Implode("\t", $Tmp_title_lines) . "\n";
    $output_string_2 = Implode("\t", $Tmp_title_lines) . "\n";
    $output_string_3 = Implode("\t", $Tmp_title_lines) . "\n";
    // print($output_string_1);
    while (!feof($fp_0))
    {
        $string = fgets($fp_0);
        $Pieces = Explode("\t", $string);
        $Pieces = Array_Map("Trim", $Pieces);
        @$uniP_id = $Pieces[42];
        // print($uniP_id."\n");
        if (!In_Array($uniP_id, $UniP_ids)) continue;
        $flag = 0;
        @$Ki = $Pieces[8];
        @$IC50 = $Pieces[9];
        @$Kd = $Pieces[10];
        if ($Ki != "")
        {
            if ($Ki[0] == ">")
            {
                $flag = 3;
                //$output_string_3 .= $line;
            }
            elseif (($Ki[0] == "<"))
            {
                $value = (float)SubStr($Ki, 1);
                if ($value <= $input_cutoff)
                {
                    $flag = 1;
                    //$output_string_1 .= $line;
                }
                else
                {
                    $flag = 3;
                    //$output_string_3 .= $line;
                }
            }
            else
            {
                $value = (float)$Ki;
                if ($value <= $input_cutoff)
                {
                    $flag = 1;
                    //$output_string_1 .= $line;
                }
                else
                {
                    $flag = 3;
                    //$output_string_3 .= $line;
                }
            }
        }
        elseif ($IC50 != "")
        {
            if ($IC50[0] == ">")
            {
                $flag = 3;
                //$output_string_3 .= $line;
            }
            elseif (($IC50[0] == "<"))
            {
                $value = (float)SubStr($IC50, 1);
                if ($value <= $input_cutoff)
                {
                    $flag = 1;
                    //$output_string_1 .= $line;
                }
                else
                {
                    $flag = 3;
                    //$output_string_3 .= $line;
                }
            }
            else
            {
                $value = (float)$IC50;
                if ($value <= $input_cutoff)
                {
                    $flag = 1;
                    //$output_string_1 .= $line;
                }
                else
                {
                    $flag = 3;
                    //$output_string_3 .= $line;
                }
            }
        }
        elseif ($Kd != "")
        {
            if ($Kd[0] == ">")
            {
                $flag = 3;
                //$output_string_3 .= $line;
            }
            elseif (($Kd[0] == "<"))
            {
                $value = (float)SubStr($Kd, 1);
                if ($value <= $input_cutoff)
                {
                    $flag = 1;
                    //$output_string_1 .= $line;
                }
                else
                {
                    $flag = 3;
                    //$output_string_3 .= $line;
                }
            }
            else
            {
                $value = (float)$Kd;
                if ($value <= $input_cutoff)
                {
                    $flag = 1;
                    //$output_string_1 .= $line;
                }
                else
                {
                    $flag = 3;
                    //$output_string_3 .= $line;
                }
            }
        }
        else
        {
            $flag = 2;
            //$output_string_2 .= $line;
        }
        $output_string = "";
        foreach ($Pieces as $key => $piece)
        {
            if (!In_Array($key, $Sel_col_keys)) continue;
            if ($output_string == "")
            {
                $output_string = $piece;
            }
            else
            {
                $output_string .= "\t{$piece}";
            }
        }
        $output_string .= "\n";
        if ($flag == 1)
        {
            $output_string_1 .= $output_string;
        }
        elseif ($flag == 2)
        {
            $output_string_2 .= $output_string;
        }
        elseif ($flag == 3)
        {
            $output_string_3 .= $output_string;
        }
    }
    FClose($fp_0);
    File_Put_Contents($output_1, $output_string_1, LOCK_EX);
    File_Put_Contents($output_2, $output_string_2, LOCK_EX);
    File_Put_Contents($output_3, $output_string_3, LOCK_EX);

?>
