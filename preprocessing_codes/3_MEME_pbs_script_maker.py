


#example script that can read the character length of a fasta file and its name to automatically create PBS script that can run MEME

for line in open('/Users/KarthikVelmurugan/Desktop/Chip-Seq/character_file.txt'):
    fields = line[:-1].split('\t')
    ID = fields[0].split('.')[0]
    ch = fields[1]
    f = open('/Users/KarthikVelmurugan/Desktop/Chip-Seq/Data/processing/all/scripts/'+str(ID)+'.pbs','w')
    f.write('#!/bin/bash\n')
    f.write('#PBS -l nodes=1\n')
    f.write('#PBS -j oe\n')
    f.write('#PBS -o $PBS_JOBID.output\n')
    f.write('#PBS -l walltime=40:00:00\n')
    f.write('#PBS -q sfx_q\n')
    f.write('#PBS -W group_list=sfx\n')
    f.write('#PBS -M velmurugan.karthikraja@gmail.com\n')
    f.write('#PBS -M delasa.aghamirzaie@gmail.com\n')
    f.write('#PBS -m be\n\n')
    f.write('/apps/packages/bio/meme/current/bin/meme -o /home/kvel/meme/new/'+str(ID)+'/ -nmotifs 20 -minw 5 -maxw 30 -dna -maxsize '+str(ch)+' /home/kvel/meme/all/'+str(fields[0])+'\n')
    f.close()



		
