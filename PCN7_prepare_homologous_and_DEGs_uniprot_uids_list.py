import pandas as pd

def read_input_file(file_path, has_header = True):
    # Efficiently read the input TSV file using pandas
    if has_header:
        return pd.read_csv(file_path, sep='\t')
    else:
        return pd.read_csv(file_path, sep='\t', header=None, names=['UniProt_ID'])

def format_to_eight_digits(number):
    return f'{number:08d}'

def main(input_file_1, input_file_2, output_file, output_file_2):
    #mol_list
    df = read_input_file(input_file_1)
    df2 = read_input_file(input_file_2, has_header=False)
    
    unique_ids = set(pd.Series(df['Query_uniprot'].unique()))  # Replace 'molecule_id' with the actual column name
    deg_ids = set(df2['UniProt_ID'].unique())
    
    all_unique_ids = unique_ids.union(deg_ids)

    with open(output_file, 'w') as output_line:
        for uid in all_unique_ids:
            output_line.write(uid + '\n')
            
    with open(output_file_2, 'w') as output_line2:
        for uid in all_unique_ids:
            output_line2.write(uid + '\n')

if __name__ == '__main__':
    INPUT_FILE_1 = './input/I_PCN7_Filtered_Homologous.txt'
    INPUT_FILE_2 = './input/I_PCN0_DEG_UniProtKB.txt'
    OUTPUT_FILE = './output/O_prepare_homologous_and_DEGs_uniprot_uids_list.txt'
    OUTPUT_FILE_2 = './input/I_prepare_homologous_and_DEGs_uniprot_uids_list.txt'
    main(INPUT_FILE_1, INPUT_FILE_2, OUTPUT_FILE, OUTPUT_FILE_2)
