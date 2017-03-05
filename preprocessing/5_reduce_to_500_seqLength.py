
#example script reduces the length of peak sequences to 500 bases

f = open('/Users/KarthikVelmurugan/Desktop/Chip-Seq/Data/processing/IBH1/IBH1_formatted_TFBSs.fa','w')
for line in open('/Users/KarthikVelmurugan/Desktop/Chip-Seq/Data/MEME_input_final/22_formatted_TFBSs.txt'):
    fields = line[:-1].split('\t')
    if len(fields[5]) < 500:
        f.write('>'+str(fields[0])+'\n'+str(fields[5])+'\n')
    else:
        m = len(fields[5])
        count = 1
        while m >= 500:
            count+=1
            new = fields[5][count:-count]
            m = len(new)
        f.write('>'+str(fields[0])+'\n'+str(new)+'\n')
