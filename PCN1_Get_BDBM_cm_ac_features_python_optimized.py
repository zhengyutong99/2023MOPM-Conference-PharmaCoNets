import pandas as pd
import os
from subprocess import Popen, PIPE

def read_input_file(file_path):
    # Efficiently read the input TSV file using pandas
    return pd.read_csv(file_path, sep='\t')

def format_to_eight_digits(number):
    return f'{number:08d}'

# Function to execute a command and get the output
def execute_command(command):
    process = Popen(command, shell=True, stdout=PIPE)
    output, _ = process.communicate()
    return output.decode().splitlines()

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
            mol_list.write(new_line)
    
    return

def process_checkmol_features(mol_list_path, checkmol_path, cm_feature_path):
    # df = read_input_file("./output/test_mol_list.txt")
    # Read the list of files
    with open(mol_list_path, 'r') as file:
        mol_files = [line.strip() for line in file]

    # Initialize the output string
    output_string_cm = "BindingDB_id"
    for index in range(1, 205):
        key = f"#{index:03d}"
        output_string_cm += f"\t{key}"
        
    output_string_cm += "\n"

    # Process each file
    for mol_file in mol_files:
        db_id = os.path.basename(mol_file)[:-4]

        checkmol_counts = {f"#{i:03d}": 0 for i in range(1, 205)}

        # Execute the external program
        cmd = f"{checkmol_path} -p {mol_file}"
        process = Popen(cmd, shell=True, stdout=PIPE)
        output_cm, _ = process.communicate()

        # Process the output from the command
        for output_cm_line in output_cm.decode().splitlines():
            pieces = output_cm_line.split(":")
            pieces = [piece.strip() for piece in pieces]
            cm_id, cm_count = pieces[0], pieces[1] if len(pieces) > 1 else 0
            checkmol_counts[cm_id] = cm_count

        # Append to the output string
        output_string_cm += db_id + "\t" + "\t".join(map(str, checkmol_counts.values())) + "\n"

    # Write the output to a file
    with open(cm_feature_path, 'w', newline='') as file:
        file.write(output_string_cm)
        
def process_atom_composition_features(mol_list_path, mod_ac_path, cm_feature_path, ac_feature_path, output_cm_ac, pcn2_input):
    # Process the first file to get the header line
    with open(mol_list_path, 'r') as file:
        mol_files = [line.strip() for line in file]
        
    cmd = f"{mod_ac_path} {mol_files[0]}"
    output_arr = execute_command(cmd)
    header_line = "\t".join([item.strip() for item in output_arr[0].split("\t")]) + "\n"

    output_string_ac = header_line
    fea_ac = {}

    # Process each file
    for mol_file in mol_files:
        path_parts = os.path.splitext(os.path.basename(mol_file))
        cmd = f"{mod_ac_path} {mol_file}"
        output_arr = execute_command(cmd)

        if len(output_arr) < 2:
            continue

        tmp_line = output_arr[1].strip()
        pieces = [item.strip() for item in tmp_line.split("\t")]

        if len(pieces) != 11:
            continue

        del pieces[0]
        result_line = "{}\t{}\n".format(path_parts[0], '\t'.join(pieces))
        output_string_ac += result_line
        fea_ac[path_parts[0]] = '\t'.join(pieces) + "\n"

    # Write to the output file
    with open(ac_feature_path, 'w') as file:
        file.write(output_string_ac)


    # Read from the output file and process
    with open(ac_feature_path, 'r') as file:
        lines = file.readlines()

    pieces = [item.strip() for item in lines[0].split("\t")]
    del pieces[0]
    header_line = "\t".join(pieces) + "\n"
    del lines[0]

    # Process each line
    for line in lines:
        pieces = [item.strip() for item in line.split("\t")]
        id = pieces[0]
        del pieces[0]
        fea_ac[id] = '\t'.join(pieces) + "\n"

    # Process the cm file
    with open(cm_feature_path, 'r') as file:
        lines = file.readlines()

    header_line = lines[0].strip() + "\t" + header_line
    with open(output_cm_ac, 'w') as file:
        file.write(header_line)
    with open(pcn2_input, 'w') as file:
        file.write(header_line)

    del lines[0]

    # Process each line
    for line in lines:
        pieces = [item.strip() for item in line.split("\t")]
        id = pieces[0]
        if id not in fea_ac:
            continue
        output_string_cm_ac = line.strip() + "\t" + fea_ac[id]
        with open(output_cm_ac, 'a') as file:
            file.write(output_string_cm_ac)
        with open(pcn2_input, 'a') as file:
            file.write(output_string_cm_ac)

def execute_external_program(program_path, input_data):
    # Execute external programs (checkmol or mod_ac)
    result = subprocess.run([program_path, input_data], stdout=subprocess.PIPE)
    return result.stdout.decode()

def process_molecule(molecule_id, checkmol_path, mod_ac_path):
    # Process each molecule ID - suitable for parallelization
    checkmol_result = execute_external_program(checkmol_path, molecule_id)
    mod_ac_result = execute_external_program(mod_ac_path, molecule_id)
    return molecule_id, checkmol_result, mod_ac_result



def main(input_file, checkmol_path, mod_ac_path):
    #mol_list
    mol_list_path = "/data/zhengyutong/Pharmaconet/Pharmaconet_drug_community_arrange/output/O_PCN1_filtered_in_10um_mol_list.txt"
    process_mol_list(input_file, mol_list_path)
       
    #checkmol_features
    cm_feature_path = "/data/zhengyutong/Pharmaconet/Pharmaconet_drug_community_arrange/output/O_PCN1_filtered_in_10um_checkmol_features.txt"
    process_checkmol_features(mol_list_path, checkmol_path, cm_feature_path)
    
    #atom_composition_features
    ac_feature_path = "./output/O_PCN1_filtered_in_10um_atom_composition_features.txt"
    cm_ac_path = "./output/O_PCN1_filtered_in_10um_cm_ac_features.txt"
    pcn2_input = "./input/I_PCN2_filtered_in_10um_cm_ac_features.txt"
    process_atom_composition_features(mol_list_path, mod_ac_path, cm_feature_path, ac_feature_path, cm_ac_path, pcn2_input)

if __name__ == '__main__':
    INPUT_FILE = './input/I_PCN1_filtered_in_10um.tsv'
    CHECKMOL_PATH = '/data/71_data/BioXGEM_tools/Drug_Teaching/Generate_features/checkmol-latest-linux-x86_64'
    # MOD_AC_PATH = '/data/71_data/BioXGEM_tools/Drug_Teaching/Generate_features/mod_ac' #old ac program
    MOD_AC_PATH = '/data/zhengyutong/Pharmaconet/ac/mod_ac'
    main(INPUT_FILE, CHECKMOL_PATH, MOD_AC_PATH)
