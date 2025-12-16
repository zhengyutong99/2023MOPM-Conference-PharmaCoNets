import os
import sys

# Before running this program need to prepare two input:I_IDmapping_input.txt, I_PCN0_BindingDB_selected_columns.txt

############################
#IDmapping
#Usage: python3 20230206_UniProt_IDmapping_online.py input_DEG.txt output_UniProtKB.txt GeneID UniProtKB output_columns.txt
############################
def IDmapping_for_PCN0():
    os.system('echo "\033[47;30m Executing UniProt-IDmapping... \033[0m"')
    os.makedirs("./log", exist_ok=True)
    os.system(f"time python Pharmaconet_UniProt_IDmapping.py 1_BRCA_logFC15_pvalue05.txt GeneID for_PCN0 > ./log/{sys.argv[1]}_IDmapping.log")
    os.system('echo "\033[47;30m UniProt-IDmapping complete \033[0m\n"')

############################
#PCN0
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
def PCN0():
    os.system('echo "\033[47;30m Executing PCN0... \033[0m"')
    os.system(f"time php PCN0_Get_DEG_inhibitors_from_BindingDB.php BindingDB_All_202401.tsv I_PCN0_DEG_UniProtKB.txt I_PCN0_BindingDB_selected_columns.txt 10000.0 > ./log/{sys.argv[1]}_PCN0.log")
    os.system('echo "\033[47;30m PCN0 complete \033[0m\n"')

############################
#Aim: Get BDBM cm ac features
#Usage:time php PCN1_Get_BDBM_cm_ac_features.php O_I_BRCA_DEG_sets_1332_filtered_in_10um.tsv 
#Input_1: O_I_BRCA_DEG_sets_1332_filtered_in_10um.tsv ("<"10 uM of IC50, EC50...) Output of PCN0
#Output_1: inhibitor mol list
#Output_2: inhibitor checkmol features
#Output_3: inhibitor atom composition (ac) features
#Output_4: inhibitor checkmol+atom composition (ac) features
############################
#############################
#Aim: Calculate tanimoto similarity between inhibitors and drugs
#Usage: time php PCN2_General_tanimoto_single_db.php O_NP1_WB_BindingDB_20222m5_filtered_in_10um_cm_ac_combined_feas.txt
#Input_1: O_NP1_WB_BindingDB_20222m5_filtered_in_10um_cm_ac_combined_feas.txt //Output of PCN0
#Output_1: tanimoto similarity between inhibitors and drugs
############################
############################
#Aim: Select_similar_inhibitor_of_drug_with_cutoff
#Usage:time php PCN3_Select_similar_inhibitor_of_drug_with_cutoff.php O_NP1_cm_ac_combined_feas_small_set_drugbank_tanimoto.txt 0.8
#Input_1: O_NP1_cm_ac_combined_feas_small_set_drugbank_tanimoto.txt
#Output_1: Tanimoto similarity between inhibitors and drugs
############################
def PCN1_2_3():
    os.system('echo "\033[47;30m Executing PCN1~3... \033[0m"')
    os.system(f"time python PCN1_2_3.py 0.8 > ./log/{sys.argv[1]}_PCN1~3.log")
    os.system('echo "\033[47;30m PCN1~3 complete \033[0m\n"')

############################
#Aim: Filter drug’s heavy atoms between 7&125
#Usage:time php PCN4_Filter_heavy_atom_number.php O_PCN3O_NP1_cm_ac_combined_feas_small_set_drugbank_tanimoto_tanimoto_similarity_0.5.txt
#Input_1: Output of drug&inihbitor similarity above cut off //output of PCN3
#Output_1: {File name of outputs}_ha_7_125.txt
############################
def PCN4():
    os.system('echo "\033[47;30m Executing PCN4... \033[0m"')
    os.system(f"time php PCN4_Filter_heavy_atom_number.php I_PCN4_filtered_in_10um_checkmol_atom_combined_features_drugbank_tanimoto_similarity_0.8.txt > ./log/{sys.argv[1]}_PCN4.log")
    os.system('echo "\033[47;30m PCN4 complete \033[0m\n"')

############################
#Aim: Mapping_targets_from_BindingDB_record
#Usage:time php PCN5_Mapping_targets_from_BindingDB_record.php O_PCN3O_NP1_cm_ac_combined_feas_small_set_drugbank_tanimoto_tanimoto_similarity_0.5.txt O_NP1_WB_BindingDB_20222m5_filtered_in_10um.tsv drugbank_small_set
#Input_1: Output of drug&inihbitor similarity above cut off //output of PCN3
#Input_2: BindingDB records with selected column //output of PCN0
#Output_1: O_PCN5_drugbank_small_set_with_target_affinity.txt
#Output_2: O_PCN5_drugbank_small_set_with_target_affinity_target_line.txt
############################
def PCN5():
    os.system('echo "\033[47;30m Executing PCN5... \033[0m"')
    os.system(f"time php PCN5_Mapping_targets_from_BindingDB_record.php I_PCN4_filtered_in_10um_checkmol_atom_combined_features_drugbank_tanimoto_similarity_0.8.txt I_PCN1_filtered_in_10um.tsv > ./log/{sys.argv[1]}_PCN5.log")
    os.system('echo "\033[47;30m PCN5 complete \033[0m\n"')

############################
#IDmapping
#Usage: time python3 20230206_UniProt_IDmapping_online.py input_DEG.txt output_UniProtKB.txt GeneID UniProtKB output_columns.txt
############################
def IDmapping_for_BLAST():
    os.system('echo "\033[47;30m Executing UniProt-IDmapping... \033[0m"')
    os.system(f"time python Pharmaconet_UniProt_IDmapping.py I_PCN0_DEG_UniProtKB.txt UniProtKB_AC-ID for_BLAST > ./log/{sys.argv[1]}_IDmapping_BLAST.log")
    os.system('echo "\033[47;30m UniProt-IDmapping complete \033[0m\n"')

############################
#BLAST
#Usage: time ncbi-blast-2.2.30+/bin/blastp -db 2363_drug_target_uniprot.fasta -query BRCA_DEG_uniprot.fasta -evalue 0.001 -outfmt 6 -out BRCA_blast_target_output.txt
############################
def PCN6():
    os.system('echo "\033[47;30m Executing PCN6-BLAST... \033[0m"')
    os.system(f"time ncbi-blast-2.2.30+/bin/blastp -db ./dataset_tools/2363_drug_target_uniprot.fasta -query ./output/O_DEG_UniProtKB_fasta.txt -evalue 0.001 -outfmt 6 -out ./output/O_PCN6_DEG_BLAST_target_output.txt > ./log/{sys.argv[1]}_BLAST.log")
    os.system('echo "\033[47;30m PCN6-BLAST complete \033[0m\n"')


############################
#Aim: Process Blast output
#Usage:time php PCN6_1_Prcoess_blast_output.php BRCA_blast_target_output.txt Filtered_BRCA_Homologous.txt Filtered_BRCA_Homologous.txt
#Input_1: BRCA_blast_target_output.txt //Blast output 
#Output_1: Filtered_BRCA_Homologous.txt // Filter E-value<10^-10; Coverage>80%, identity>30%
############################
def PCN6_1():
    os.system('echo "\033[47;30m Executing PCN6-1... \033[0m"')
    os.system(f"time php PCN6_1_Prcoess_blast_output.php O_PCN6_DEG_BLAST_target_output.txt O_PCN6-1_Filtered_Homologous.txt I_PCN7_Filtered_Homologous.txt > ./log/{sys.argv[1]}_PCN6-1.log")
    os.system('echo "\033[47;30m PCN6-1 complete \033[0m\n"')

############################
#Aim: Prepare_DEG_and_homologous_uniprot_info
#Usage: time python3 PCN7_prepare_DEG_and_homologous_uniprot_info.py
############################
def prepare_PCN7_input():
    os.system('echo "\033[47;30m Preparing DEG and homologous uniprot info... \033[0m"')
    os.system('time python3 PCN7_prepare_homologous_and_DEGs_uniprot_uids_list.py')
    os.system(f"time python Pharmaconet_UniProt_IDmapping.py I_prepare_homologous_and_DEGs_uniprot_uids_list.txt UniProtKB_AC-ID for_PCN7 > ./log/{sys.argv[1]}_IDmapping_prepare_homologous_DEGs_info.log")
    os.system('echo "\033[47;30m Preparing DEG and homologous uniprot info complete \033[0m\n"')


############################
#Aim: Merge to form drug community
#Usage:time php PCN7_Merge_to_community.php I_BRCA_DEG_sets_1332.txt DEG_and_homologous_uniprot_info.txt ../drugbank_approved_BRCA_with_target_affinity_target_line.txt ../PCN6_Process_DEG_homologous/Filtered_BRCA_Homologous.txt output.txt
#Input_1: DEG list (I_BRCA_DEG_sets_1332.txt PCN0 input)
#Input_2: Uniprot mapping gene/protein name info (DEG_and_homologous_uniprot_info.txt)
#Input_3: Output of PCN5 compound similarity info (../drugbank_approved_BRCA_with_target_affinity_target_line.txt.txt)
#Input_4: Output of PCN6 homologous info (../PCN6_Process_DEG_homologous/Filtered_BRCA_Homologous.txt.txt)
#Ouput: output of drug community (Output.txt)
############################
def PCN7():
    os.system('echo "\033[47;30m Executing PCN7... \033[0m"')
    os.system("time php PCN7_Merge_to_community.php I_PCN0_DEG_UniProtKB.txt I_PCN7_DEG_and_homologous_uniprot_info.txt I_PCN7_drugbank_small_set_with_target_affinity_target_line.txt I_PCN7_Filtered_Homologous.txt O_PCN7_drug_community.txt")
    os.system('echo "\033[47;30m PCN7 complete \033[0m\n"')

if __name__ == '__main__':
    IDmapping_for_PCN0()
    PCN0()
    PCN1_2_3()
    PCN4()
    PCN5()
    IDmapping_for_BLAST()
    PCN6()
    PCN6_1()
    prepare_PCN7_input()
    PCN7()