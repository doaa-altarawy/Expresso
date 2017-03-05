import sys
args = sys.argv[1:]
x = args[0]
y = args[1]
o = args[2]

ref_flat_file = open(x, 'r+')
ref_flat_lines = ref_flat_file.readlines()


functional_dec = open(y, 'r+')
functional_dec_lines = functional_dec.readlines()


gene_tbl_file = open(o, 'w+')

function_dic = {}

#functional description dictionary
for j in range(1, len(functional_dec_lines)):
	line = functional_dec_lines[j]
	lineAr = line.split('\t')
	
	transcript_name =  lineAr[0].strip()
	#print gene_name
	annotation = lineAr[2].strip()

	function_dic[transcript_name] = annotation

print len(function_dic.keys())

#print function_dic['AT1G01010']

outputlines = []
for i in range(len(ref_flat_lines)):
	line = ref_flat_lines[i]
	lineAr = line.split('\t')
	
	gene_name = lineAr[0].strip()

	gene_id = lineAr[1].split('.')[0]
	transcript_id = lineAr[1].strip()

	annotation = function_dic[transcript_id]

	outline = gene_id + '\t' + gene_name + '\t' + transcript_id  + '\t' + annotation + '\t' + 'Arabidopsis thaliana' + '\n'
	outputlines.append(outline)


gene_tbl_file.writelines(outputlines)
