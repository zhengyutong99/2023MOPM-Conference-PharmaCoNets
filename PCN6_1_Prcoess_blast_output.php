<?php
############################
#Aim: Process Blast output
#Usage:time php PCN6_1_Prcoess_blast_output.php BRCA_blast_target_output.txt Filtered_BRCA_Homologous.txt 
#Input_1: BRCA_blast_target_output.txt //Blast output 
#Output_1: Filtered_BRCA_Homologous.txt // Filter E-value<10^-10; Coverage>80%, identity>30%
############################

    ini_set("memory_limit", -1);
    Error_Reporting(-1);

    $input = "./output/".$argv[1]; //Input_1: Output of Blast
	$Lines = File($input);

	file_put_contents("./output/".$argv[2], "");
	$output_string = "DEG_uniprot\tQuery_uniprot\tIdentities\tE-value\t'-log(Evalue)\tCoverage\n";
    foreach ($Lines as $line)
    {
        $Pieces = Explode("\t", $line);
        $Pieces = Array_Map("Trim", $Pieces);
        $Query = Explode("|", $Pieces[0]);
		$query = $Query[1];
		$Target = Explode("|", $Pieces[1]);
		$target = $Target[1];
		$identity = $Pieces[2];
		$evalue = $Pieces[10];
		$log_evalue = -log($Pieces[10],10);
		$log_evalue = Round($log_evalue,3);
		$coverage = ($Pieces[7]-$Pieces[6]+1)/$Pieces[3];
		$coverage = Round($coverage,3);
		#E-value<10^-10; Coverage>80%, identity>30%
		if($evalue <= pow(10,-10) && $coverage > 0.8 && $identity > 0.3){
			$output_string .= $query."\t".$target."\t".$identity."\t".$evalue."\t".$log_evalue."\t".$coverage."\n";
		}
	}
	file_put_contents("./output/".$argv[2], $output_string, FILE_APPEND);
	file_put_contents("./input/".$argv[3], $output_string, FILE_APPEND);







?>