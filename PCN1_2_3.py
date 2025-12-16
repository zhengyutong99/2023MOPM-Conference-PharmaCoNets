import pandas as pd
import os
import sys

def read_input_file(file_path):
    # Efficiently read the input TSV file using pandas
    return pd.read_csv(file_path, sep='\t')

def format_to_eight_digits(number):
    return f'{number:08d}'

def process_mol_list(input_file, mol_list_path):
    df = read_input_file(input_file)
    molecule_ids = set(pd.Series(df['BindingDB MonomerID'].unique()).apply(format_to_eight_digits))  # Replace 'molecule_id' with the actual column name
    # mol = open("/ngs_data/backup/ychsu/5732_HW/Compound_DB/BindingDB_data/BindingDB_mol/list_BindingDB_All_mol_2020m10.txt")
    mol = open("/data/240_data/zhengyutong/BindingDB202401/list_BindingDB_All_mol_202401.txt")
    mol_list = open(mol_list_path, 'w')
    
    for m in mol.readlines():
        if ".mol" in m:
            file_name_with_extension = m.strip().split('/')[-1]
            file_name = file_name_with_extension.replace('.mol', '').replace('BDBM', "")
        if file_name in molecule_ids:
            # Need to mount 240/data on your server to read .mol files
            # new_line = m.replace("/data/", "/data/240_data/") # For list_BindingDB_All_mol_2020m10.txt version
            # new_line = m.replace("/data/", "/data/240_data/") # For list_BindingDB_All_mol_202401.txt version
            new_line = file_name_with_extension.replace('.mol', '')
            mol_list.write(new_line + "\n")
    
    return

def read_compound_ids(filename):
    with open(filename, 'r') as f:
        return [line.strip() for line in f.readlines()]

def group_compound_ids_by_file(compound_ids):
    groups = {}
    for id in compound_ids:
        file_prefix = id[:8]
        if file_prefix not in groups:
            groups[file_prefix] = []
        groups[file_prefix].append(id)
    return groups

def filter_data_by_threshold(filenames, compound_id_groups, threshold, output):
    for file_prefix, ids in compound_id_groups.items():
        filename = f"/data/zhengyutong/Pharmaconet/0_all_2024BindingDB_2024Drugbankapproved_tanimoto/BindingDB_Drugbankapproved_tanimoto_cm_ac_{file_prefix}.txt"
        # if filename in filenames:
        try:
            with open(filename, 'r') as f:
                headers = f.readline().strip().split('\t')
                id_index = {header: i for i, header in enumerate(headers) if header in ids}
                for line in f:
                    parts = line.strip().split('\t')
                    drug_id = parts[0]
                    for header, index in id_index.items():
                        value = float(parts[index])
                        if value > threshold:
                            output.append(f"{drug_id}\t{header}\t{value}")
        except FileNotFoundError:
            continue
    
    output.sort()

def PCN1_2_3(mol_list_path):
    if len(sys.argv) != 2:
        print("Usage: python3 PCN1_2_3.py <threshold>")
        sys.exit(1)

    threshold = float(sys.argv[1])
    # threshold = 0.8
    compound_ids = read_compound_ids(mol_list_path)
    compound_id_groups = group_compound_ids_by_file(compound_ids)
    
    output_data = []
    filter_data_by_threshold(set(compound_id_groups.keys()), compound_id_groups, threshold, output_data)
    # processed_files = set()

    # for compound_id in compound_ids:
    #     file_prefix = compound_id[:8]
    #     filename = f"/data/zhengyutong/Pharmaconet/0_all_2024BindingDB_2024Drugbankapproved_tanimoto/BindingDB_Drugbankapproved_tanimoto_cm_ac_{file_prefix}.txt"
    #     if filename not in processed_files:
    #         try:
    #             filter_data_by_threshold(filename, compound_ids, threshold, output_data)
    #         except FileNotFoundError:
    #             continue

    #         processed_files.add(filename)
    
    # with open('/data/zhengyutong/Pharmaconet/0_Pharmaconet_drug_community_arrange_202402/output_test/test_PCN123_output.txt', 'w') as f:
    with open(f'./output/O_PCN3_filtered_in_10um_checkmol_atom_combined_features_drugbank_tanimoto_similarity_{sys.argv[1]}.txt', 'w') as f:
        for line in output_data:
            f.write(line + '\n')

    with open(f'./input/I_PCN4_filtered_in_10um_checkmol_atom_combined_features_drugbank_tanimoto_similarity_{sys.argv[1]}.txt', 'w') as f:
        for line in output_data:
            f.write(line + '\n')
    
if __name__ == '__main__':
    input_file = './input/I_PCN1_filtered_in_10um.tsv'
    mol_list_path = "./output/O_PCN1_filtered_in_10um_mol_list.txt"

    process_mol_list(input_file, mol_list_path)
    PCN1_2_3(mol_list_path)