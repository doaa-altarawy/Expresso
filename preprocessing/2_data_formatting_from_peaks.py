

#example code that extracts only the needed information from the peak files


f = open('/Users/KarthikVelmurugan/Desktop/Chip-Seq/Data/processing/1_Good/GSM1142628_AP1_8days_formatted.txt','w')
for line in open('/Users/KarthikVelmurugan/Desktop/Chip-Seq/Data/processing/1_Good/GSM1142628_AP1_8days.txt'):
    if not line.startswith('peak'):
        fields = line.split('\t')
        ar = []
        for x in range(len(fields)):
            if not len(fields[x]) == 0:
                if not fields[x] == '\r\n':
                    if int(x) > 7:
                        ar.append(fields[x])
        gene = ','.join(ar)
        if not gene == 'NA':
            f.write(str(fields[0])+'\t'+str(fields[1])+'\t'+str(fields[2])+'\t'+fields[3]+'\t'+str(gene)+'\n')

f.close()


f = open('/Users/KarthikVelmurugan/Desktop/Chip-Seq/Data/processing/16/GSE45846_SOC1_SUMMARY_formatted.txt','w')


for line in open('/Users/KarthikVelmurugan/Desktop/Chip-Seq/Data/processing/16/GSE45846_SOC1_SUMMARY.txt'):
    if not line.startswith('#?'):
        fields = line[:-1].split('\t')
        ar = []
        for x in range(len(fields)):
            if fields[x].startswith('"AT'):
                if not 'RNA' in fields[x]:
                    ar.append(fields[x].split('"')[1])
        gene = ','.join(ar)
        end = int(fields[2])+int(fields[3])
        f.write(str(fields[0])+'\t'+str(fields[1])+'\t'+str(fields[2])+'\t'+str(end)+'\t'+str(gene)+'\n')
				
                
