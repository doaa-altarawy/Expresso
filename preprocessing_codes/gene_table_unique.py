import sys
args = sys.argv[1:]
x= args[0]
y = args[1]

inputfile = open(x, 'r+')
inputlines = inputfile.readlines()
outputlines = []
gene_dic = {}
outputlines = []
for i in range(len(inputlines)):
	line = inputlines[i].strip('\n')
	lineAr  = line.split('\t') 
  
        desc = lineAr[0] + '\t' + lineAr[1] + '\t' + lineAr[3] + '\t' + lineAr[4] 

        if gene_dic.has_key(lineAr[0].strip()):
        	gene_dic[lineAr[0].strip()] = desc
        else:   
                gene_dic[lineAr[0].strip()] = desc     
                outputlines.append(desc + '\n')
                print desc


outputfile = open(y, 'w+')
outputfile.writelines(outputlines) 
