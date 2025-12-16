<?php
############################
#Aim: Merge to form drug community
#Usage:time php PCN7_Merge_to_community.php I_BRCA_DEG_sets_1332.txt DEG_and_homologous_uniprot_info.txt ../drugbank_approved_BRCA_with_target_affinity_target_line.txt ../PCN6_Process_DEG_homologous/Filtered_BRCA_Homologous.txt output.txt
#Input_1: DEG list (I_BRCA_DEG_sets_1332.txt)
#Input_2: Uniprot mapping gene/protein name info (DEG_and_homologous_uniprot_info.txt)
#Input_3: Output of PCN5 compound similarity info (../drugbank_approved_BRCA_with_target_affinity_target_line.txt.txt)
#Input_4: Output of PCN6 homologous info (../PCN6_Process_DEG_homologous/Filtered_BRCA_Homologous.txt.txt)
#Ouput: output of drug community (Output.txt)
############################
    ini_set("memory_limit", -1);
    Error_Reporting(-1);

    $input_DEGs = "./input/".$argv[1]; //"I_BRCA_DEG_sets_1332.txt";
    $input_protein_name = "./input/".$argv[2];//"DEG_and_homologous_uniprot_info.txt";
	#Direct target
    // $input_dt = "/data/71_data/CompoundDB/DrugBank/Drug_2303_DrugBank_drug-target-pairs_with_name_20200702.txt";
    $input_dt = "/data/240_data/zhengyutong/DrugBank202401/Drug_2614_DrugBank_drug-target-pairs_with_name_202401.txt";
    #compound similarity
	$input_cs = "./input/".$argv[3]; //"drugbank_approved_BRCA_with_target_affinity_target_line.txt";
    #Homologous
	$input_ts = "./input/".$argv[4]; //"../PCN6_Process_DEG_homologous/Filtered_BRCA_Homologous.txt.txt";
    #$input_gene_feas = "gene_feature_v3.txt";
    //$output = "NP_Union_WB_2303_Drugs_Target_drug_pair_Homopharma_BDBM_target_Homologus_merged_filtered_DEGs.txt";
    $output = "./output/".$argv[5]; //"WB_2303_Drugs_Target_drug_pair_Homopharma_BDBM_target_Homologus_merged_filtered_DEGs.txt";
    $DEGs = Array();
    $DT_drug = Array();
    $DT_target = Array();
    $Protein_name = Array();
	/*
    $P_value = Array();
    $FC = Array();
    $Nor_DEP = Array();
    $Nor_DSubSys = Array();
    $Nor_DSys = Array();
    $MetaZ_SubSys = Array();
    $MeatZ_Sys = Array();
    $P_value_Ori = Array();
    $FC_Ori = Array();
	*/

    $DEGs = File($input_DEGs);
    $DEGs = Array_Map("Trim", $DEGs);

    $Lines = File($input_protein_name);
    unset ($Lines[0]);
    foreach ($Lines as $line)
    {
        $Pieces = Explode("\t", $line);
        $Pieces = Array_Map("Trim", $Pieces);
        $uniP_id = $Pieces[0];
        $name = $Pieces[2];
        $Protein_name[$uniP_id] = $name;
    }
/*
    $Lines = File($input_gene_feas);
    unset ($Lines[0]);
    foreach ($Lines as $line)
    {
        $Pieces = Explode("\t", $line);
        $Pieces = Array_Map("Trim", $Pieces);
        $uniP_id = $Pieces[0];
        $pv = $Pieces[1];
        $fc = $Pieces[2];
        $nor_dep = $Pieces[4];
        $nor_dsubsys = $Pieces[6];
        $nor_dsys = $Pieces[8];
        $metaz_subsys = $Pieces[9];
        $meatz_sys = $Pieces[10];
        $ori_pv = $Pieces[11];
        $ori_fc = $Pieces[12];
        $P_value[$uniP_id] = $pv;
        $FC[$uniP_id] = $fc;
        $Nor_DEP[$uniP_id] = $nor_dep;
        $Nor_DSubSys[$uniP_id] = $nor_dsubsys;
        $Nor_DSys[$uniP_id] = $nor_dsys;
        $MetaZ_SubSys[$uniP_id] = $metaz_subsys;
        $MeatZ_Sys[$uniP_id] = $meatz_sys;
        $P_value_Ori[$uniP_id] = $ori_pv;
        $FC_Ori[$uniP_id] = $ori_fc;
    }
*/
    $output_string = "DrugBank_ID\tDrug_Name\tTarget_UniProt_ID\tTarget_Name\tBDBM_id\tBDBM_target_UniProt_id\tBDBM_target_Protein_Name\tCompound_Similarity\t";
    $output_string .= "Homologus_UniProt_id\tHomologus_Protein_Name\tIdentity\tE-value\tCoverage\t";
    #$output_string .= "Text_mining_Similar_drug_DB_id\tSimilar_drug_target_UniProt_id\tSimilar_drug_target_Protein_Name\tCompound_Similarity\t";
    #$output_string .= "P_value\tFC\tNor_DEP\tNor_DSubSys\tNor_DSys\tMetaZ_SubSys\tMeatZ_Sys\tP_value_Ori\tFC_Ori\t";
    $output_string .= "Type\tTarget\tTarget name\tCS\tIDEN\n";

    $Lines = File($input_dt);
    unset ($Lines[0]);
    $type = 1;  //Original drug-target pair
    foreach ($Lines as $line)
    {
        $Pieces = Explode("\t", $line);
        $Pieces = Array_Map("Trim", $Pieces);
        $drug_db_id = $Pieces[0];
        $drug_name = $Pieces[1];
        $target_unip_id = $Pieces[2];
        $target_name = $Pieces[3];
        $dt_list = "{$drug_db_id}\t{$drug_name}\t{$target_unip_id}\t{$target_name}";
        if (!Array_Key_exists($drug_db_id, $DT_drug))
        {
            $DT_drug[$drug_db_id] = Array();
        }
        Array_Push($DT_drug[$drug_db_id], $dt_list);
        if (!Array_Key_exists($target_unip_id, $DT_target))
        {
            $DT_target[$target_unip_id] = Array();
        }
        Array_Push($DT_target[$target_unip_id], $dt_list);

        if (!In_Array($target_unip_id, $DEGs))  continue;
        $output_string .= "{$dt_list}\t-\t-\t-\t-\t";
        $output_string .= "-\t-\t-\t-\t-\t";
        $output_string .= "{$type}\t{$target_unip_id}\t{$target_name}\t-\t-\n";
        #if (!Array_Key_Exists($target_unip_id, $P_value))
        #{
            #$output_string .= "0\t0\t0\t0\t0\t0\t0\t0\t0\t{$type}\n";
        #}
        #else
        #{
            #$output_string .= "{$P_value[$target_unip_id]}\t{$FC[$target_unip_id]}\t{$Nor_DEP[$target_unip_id]}\t";
            #$output_string .= "{$Nor_DSubSys[$target_unip_id]}\t{$Nor_DSys[$target_unip_id]}\t";
            #$output_string .= "{$MetaZ_SubSys[$target_unip_id]}\t{$MeatZ_Sys[$target_unip_id]}\t";
            #$output_string .= "{$P_value_Ori[$target_unip_id]}\t{$FC_Ori[$target_unip_id]}\t";
            #$output_string .= "{$type}\t{$target_unip_id}\t-\t-\n";
        #}
    }

    $Lines = File($input_cs);
	#print_r($Lines);
    unset ($Lines[0]);
    $type = 2;  //Compound similar from BDB
    foreach ($Lines as $line)
    {
        $Pieces = Explode("\t", $line);
        $Pieces = Array_Map("Trim", $Pieces);
        $drug_db_id = $Pieces[0];
        $bdbm_id = $Pieces[1];
        $bdbm_target_unip_id = $Pieces[3];
        if (!In_Array($bdbm_target_unip_id, $DEGs)) continue;
        $bdbm_target_protein_name = $Protein_name[$bdbm_target_unip_id];
		
        $cs = $Pieces[2];
        if (!Array_Key_Exists($drug_db_id, $DT_drug))   continue;
        foreach ($DT_drug[$drug_db_id] as $dt_list)
        {
            $output_string .= "{$dt_list}\t{$bdbm_id}\t{$bdbm_target_unip_id}\t{$bdbm_target_protein_name}\t{$cs}\t";
            $output_string .= "-\t-\t-\t-\t-\t";
            $output_string .= "{$type}\t{$bdbm_target_unip_id}\t{$bdbm_target_protein_name}\t{$cs}\t-\n";
            #if (!Array_Key_Exists($bdbm_target_unip_id, $P_value))
            #{
                #$output_string .= "0\t0\t0\t0\t0\t0\t0\t0\t0\t{$type}\n";
            #}
            #else
            #{
                #$output_string .= "{$P_value[$bdbm_target_unip_id]}\t{$FC[$bdbm_target_unip_id]}\t{$Nor_DEP[$bdbm_target_unip_id]}\t";
                #$output_string .= "{$Nor_DSubSys[$bdbm_target_unip_id]}\t{$Nor_DSys[$bdbm_target_unip_id]}\t";
                #$output_string .= "{$MetaZ_SubSys[$bdbm_target_unip_id]}\t{$MeatZ_Sys[$bdbm_target_unip_id]}\t";
                #$output_string .= "{$P_value_Ori[$bdbm_target_unip_id]}\t{$FC_Ori[$bdbm_target_unip_id]}\t";
                #$output_string .= "{$type}\t{$bdbm_target_unip_id}\t{$cs}\t-\n";
            #}
        }
    }

    $Lines = File($input_ts);
    unset ($Lines[0]);
    $type = 3;  //Homologus protein
    foreach ($Lines as $line)
    {
        $Pieces = Explode("\t", $line);
        $Pieces = Array_Map("Trim", $Pieces);
        $target_unip_id = $Pieces[0];
        $homo_unip_id = $Pieces[1];
        if (!In_Array($homo_unip_id, $DEGs))    continue;
        $homo_protein_name = $Protein_name[$homo_unip_id];
        $iden = $Pieces[2];
        #$nor_iden = $Pieces[5];
        $ev = $Pieces[3];
        $nor_ev = $Pieces[4];
        $coverage = $Pieces[5];
        if (!Array_Key_Exists($target_unip_id, $DT_target)) continue;
        foreach ($DT_target[$target_unip_id] as $dt_list)
        {
            $output_string .= "{$dt_list}\t-\t-\t-\t-\t";
            $output_string .= "{$homo_unip_id}\t{$homo_protein_name}\t{$iden}\t{$ev}\t{$coverage}\t";
            $output_string .= "{$type}\t{$homo_unip_id}\t{$homo_protein_name}\t-\t{$iden}\n";
            #if (!Array_Key_Exists($homo_unip_id, $P_value))
            #{
                #$output_string .= "0\t0\t0\t0\t0\t0\t0\t0\t0\t{$type}\n";
            #}
            #else
            #{
                #$output_string .= "{$P_value[$homo_unip_id]}\t{$FC[$homo_unip_id]}\t{$Nor_DEP[$homo_unip_id]}\t";
                #$output_string .= "{$Nor_DSubSys[$homo_unip_id]}\t{$Nor_DSys[$homo_unip_id]}\t";
                #$output_string .= "{$MetaZ_SubSys[$homo_unip_id]}\t{$MeatZ_Sys[$homo_unip_id]}\t";
                #$output_string .= "{$P_value_Ori[$homo_unip_id]}\t{$FC_Ori[$homo_unip_id]}\t";
                #$output_string .= "{$type}\t{$homo_unip_id}\t-\t{$iden}\n";
            #}
        }
    }
/*
    $Lines = File($input_tm);
    unset ($Lines[0]);
    $type = 4;  //Text mining drug-target pair
    foreach ($Lines as $line)
    {
        $Pieces = Explode("\t", $line);
        $Pieces = Array_Map("Trim", $Pieces);
        $drug_db_id = $Pieces[0];
        $sim_drug_db_id = $Pieces[5];
        $sim_drug_target_unip_id = $Pieces[3];
        if (!In_Array($sim_drug_target_unip_id, $DEGs)) continue;
        $sim_drug_target_protein_name = $Protein_name[$sim_drug_target_unip_id];
        $sim_drug_cs = $Pieces[1];
        if (!Array_Key_Exists($drug_db_id, $DT_drug))   continue;
        foreach ($DT_drug[$drug_db_id] as $dt_list)
        {
            $output_string .= "{$dt_list}\t-\t-\t-\t-\t";
            $output_string .= "-\t-\t-\t-\t-\t-\t-\t";
            $output_string .= "{$sim_drug_db_id}\t{$sim_drug_target_unip_id}\t{$sim_drug_target_protein_name}\t{$sim_drug_cs}\t";
            if (!Array_Key_Exists($sim_drug_target_unip_id, $P_value))
            {
                $output_string .= "0\t0\t0\t0\t0\t0\t0\t0\t0\t{$type}\n";
            }
            else
            {
                $output_string .= "{$P_value[$sim_drug_target_unip_id]}\t{$FC[$sim_drug_target_unip_id]}\t{$Nor_DEP[$sim_drug_target_unip_id]}\t";
                $output_string .= "{$Nor_DSubSys[$sim_drug_target_unip_id]}\t{$Nor_DSys[$sim_drug_target_unip_id]}\t";
                $output_string .= "{$MetaZ_SubSys[$sim_drug_target_unip_id]}\t{$MeatZ_Sys[$sim_drug_target_unip_id]}\t";
                $output_string .= "{$P_value_Ori[$sim_drug_target_unip_id]}\t{$FC_Ori[$sim_drug_target_unip_id]}\t";
                $output_string .= "{$type}\t{$sim_drug_target_unip_id}\t{$sim_drug_cs}\t-\n";
            }
        }
    }
*/
    File_Put_Contents($output, $output_string, LOCK_EX);

?>
