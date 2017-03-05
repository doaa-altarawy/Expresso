import math
import sys
args = sys.argv[1:]

#x = args[0] #*.fa file 
#y1 = args[1] #start position
#y2 = args[2] # end position
#o = args[3] #seq in the start and end position
y = args[0] # input file
o = args[1]
o_fa = args[2]

#fa_files = ['Glycine_max.V1.0.18.dna.chromosome.Gm01.fa','Glycine_max.V1.0.18.dna.chromosome.Gm02.fa','Glycine_max.V1.0.18.dna.chromosome.Gm03.fa','Glycine_max.V1.0.18.dna.chromosome.Gm04.fa','Glycine_max.V1.0.18.dna.chromosome.Gm05.fa','Glycine_max.V1.0.18.dna.chromosome.Gm06.fa','Glycine_max.V1.0.18.dna.chromosome.Gm07.fa','Glycine_max.V1.0.18.dna.chromosome.Gm08.fa','Glycine_max.V1.0.18.dna.chromosome.Gm09.fa','Glycine_max.V1.0.18.dna.chromosome.Gm10.fa','Glycine_max.V1.0.18.dna.chromosome.Gm11.fa','Glycine_max.V1.0.18.dna.chromosome.Gm12.fa','Glycine_max.V1.0.18.dna.chromosome.Gm13.fa','Glycine_max.V1.0.18.dna.chromosome.Gm14.fa','Glycine_max.V1.0.18.dna.chromosome.Gm15.fa','Glycine_max.V1.0.18.dna.chromosome.Gm16.fa','Glycine_max.V1.0.18.dna.chromosome.Gm17.fa','Glycine_max.V1.0.18.dna.chromosome.Gm18.fa','Glycine_max.V1.0.18.dna.chromosome.Gm19.fa','Glycine_max.V1.0.18.dna.chromosome.Gm20.fa']

fa_files = ['Arabidopsis_thaliana.TAIR10.21.dna.chromosome.1.fa', 'Arabidopsis_thaliana.TAIR10.21.dna.chromosome.2.fa', 'Arabidopsis_thaliana.TAIR10.21.dna.chromosome.3.fa', 'Arabidopsis_thaliana.TAIR10.21.dna.chromosome.4.fa', 'Arabidopsis_thaliana.TAIR10.21.dna.chromosome.5.fa']#, 'Arabidopsis_thaliana.TAIR10.21.dna.chromosome.Pt.fa', 'Arabidopsis_thaliana.TAIR10.21.dna.chromosome.Mt.fa']

#fa_file = open(x, 'r+')
#fa_lines = fa_file.readlines()


gtf_file = open(y, 'r+')
gtf_lines = gtf_file.readlines()


fa_file = open(o_fa, 'w+')
def find_chromosome(chr):
#	print chr
	#if chr[2] == '0':
	#	chrAr = chr.split('Gm0')
	#else:
	#	chrAr = chr.split('Gm')
#	print chr	
	mychr = int(chr) - 1
	myfa_file = open(fa_files[mychr], 'r+')
        myfa_lines = myfa_file.readlines()
	#print len(myfa_lines)
	return myfa_lines


def seqfinder(start_pos, end_pos, fa_lines):
	start_line = int(math.floor(start_pos/60)) + 1
	#print start_line
	end_line = int(math.floor(end_pos/60)) + 1
	#print end_line

	rem1 = start_pos%60
#	print rem1
	rem2 = end_pos%60
#	print rem2
	if start_line == end_line:
                bps = fa_lines[end_line][rem1-1: rem2]
                #print fa_lines[end_line-1]
                #print fa_lines[end_line]
                #print fa_lines[end_line + 1]
        else:
                bps = ''
                bps_first = fa_lines[start_line][rem1 - 1:]
                bps = bps_first.strip('\n')
                bps_end = fa_lines[end_line][:rem2].strip('\n')
                for i in range(start_line + 1, end_line):
                        line = fa_lines[i]
                        bps = bps + line.strip('\n')


                bps = bps + bps_end

        return bps



outputlines = []
fa_lines = []
prv_chr = ''



allowed_chr = ['1', '2', '3', '4', '5']
#input file format: 67.22   2       15100023        15100437
for i in range(len(gtf_lines)):
	gtf_line = gtf_lines[i].strip('\n')
	gtf_lineAr = gtf_line.split('\t')
	
	if len(gtf_lineAr) > 3:
		#print i	
		#print gtf_lineAr
		
	#	print 'len(gtf_lineAr) > 3:'
		cur_chr = gtf_lineAr[1].strip() #chromose number
		#print cur_chr
		#print '------'
		if cur_chr.find('scaffold') != -1:
			break
	
		if cur_chr.find('mt') != -1 | cur_chr.find('Mt')!= -1 | cur_chr.find('MT')!= -1:
			cur_chr = '7'
	
		if cur_chr.find('pt')!= -1 | cur_chr.find('Pt')!= -1 | cur_chr.find('PT')!= -1:
        	       cur_chr = '6'

	#print cur_chr
		if cur_chr in allowed_chr:
		#	print cur_chr, 'Delaasa'
			if cur_chr != prv_chr:
				myfa_lines = find_chromosome(cur_chr)
	
			start_position = int(gtf_lineAr[2])
			end_position = int(gtf_lineAr[3])
		
		
			myseq = seqfinder(start_position, end_position, myfa_lines)	
		
			#if ((len(myseq) >= 20) & (len(myseq) <=700)):	
			if (len(myseq) >= 20):
				outline = gtf_line + '\t' + myseq
				outline.strip('\n')
				outputlines.append(outline + '\n')

				fa_line = '>' + gtf_line + '\n' + myseq + '\n'
				fa_lines.append(fa_line)
				

			prv_chr = cur_chr


	else:
		i = i + 1


#gm = [0, 931929 - 1, 1792876 -1, 2589229 -1, 3409961-1,4108904-1,4954286-1,5699007-1,6482267-1,7262998-1,8112493-1,8765374-1,9433928-1,10174079-1,11002601-1,11851588-1,12474879-1,13173327-1,14211797-1,15054956-1]
#for i in gm:
#	print fa_lines[i]


outputfile = open(o, 'w+')
outputfile.writelines(outputlines)

fa_file.writelines(fa_lines)
fa_file.close()
outputfile.close()

