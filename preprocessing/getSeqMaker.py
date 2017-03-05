import os

file_names = ['4_final_formatted' , '13_unique_format', '17_formatted', '22_formatted','26_AP3_unique' , '27_formatted', '29_formatted', '37_formatted', 'GSE45846_SOC1_SUMMARY_formatted', 'GSE46986_AP1_2days_formatted', 'GSE46986_SEP3_2days_formatted', 'GSE48081_KAN1_SUMMARY_formatted', 'GSE48082_FLM_BETA_SUMMARY_formatted','GSE48082_FLM_DELTA_SUMMARY_formatted', 'GSE48082_FLM_SUMMARY_formatted', 'GSM878068_formatted', 'GSM1142624_SEP3_4days_formatted', 'GSM1142624_SEP3_8days_formatted', 'GSM1142627_AP1_4days_formatted', 'GSM1142628_AP1_8days_formatted']


print len(file_names)


for i in file_names:
	sort_cmd = 'sort -k 2 -g ' + i + '.txt'  + ' > ' + i + '_sorted'
	print sort_cmd
	os.system(sort_cmd)	
	
	getSeqCmd = 'python seq3.py ' + i + '_sorted ' + i + '_TFBSs.txt ' + i + '_TFBS.fa'	
	print getSeqCmd
	
	os.system(getSeqCmd)
	print '---------------------------------------------------------------------------------------------------'
