#!/usr/bin/env python
import sys
args = sys.argv[1:]

#ar1 = [1,1,1,2]
#ar2 = [1,1,1,2]

x = args[0]
y = args[1]
import math

x_ar = x.split(',')
y_ar = y.split(',')

x_ar_F = []
for myx in x_ar:
	x_ar_F.append(float(myx))

y_ar_F = []
for myy in y_ar:
        y_ar_F.append(float(myy))

#print x
#print y

#print x_ar_F
#print y_ar_F


def average(x):
    assert len(x) > 0
#    print float(sum(x)) / len(x)
    return float(sum(x)) / len(x)

def pearson_def(x, y):
    assert len(x) == len(y)
    n = len(x)
    assert n > 0
    avg_x = average(x)
    avg_y = average(y)
    diffprod = 0
    xdiff2 = 0
    ydiff2 = 0
    for idx in range(n):
        xdiff = x[idx] - avg_x
        ydiff = y[idx] - avg_y
        diffprod += xdiff * ydiff
        xdiff2 += xdiff * xdiff
        ydiff2 += ydiff * ydiff

    return str('%.4f'%(float(diffprod / math.sqrt(xdiff2 * ydiff2))))
print  str('%.4f'%(float(pearson_def(x_ar_F,y_ar_F))))

