
#example code that shows extarction of peak data from user uploaded excel sheets

from xlrd import open_workbook

infile = open_workbook('/Users/KarthikVelmurugan/Desktop/Chip-Seq/Data/processing/16/new_SOC.xlsx')
f = open('/Users/KarthikVelmurugan/Desktop/Chip-Seq/Data/processing/16/new_SOC.txt','w')
for s in infile.sheets():
	for row in range(s.nrows):
		ar = []
		for col in range(s.ncols):
			ar.append(str(s.cell(row,col).value))

		line = '\t'.join(ar)
		f.write(str(line)+'\n')
f.close()

		
