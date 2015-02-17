/**
 Merge a wikipedia pagelink dump and ns0 page title dump.
 Keep only red links.
 
 To compile: gcc redlinkmerge.c -o redlinkmerge
 To run: ./redlinkmerge file1 file2
 
*/

#define _FILE_OFFSET_BITS 64

#include <stdio.h>
#include <string.h>

#define BUF_SIZE 10000
//#ifdef __APPLE__
#define rawmemchr(b,c)	memchr(b,c,BUF_SIZE)
//#endif

enum actions {WRITE_LEFT, WRITE_RIGHT, MERGE_BOTH};

int main(int argc,char** argv){
	char buf1[BUF_SIZE];
	char buf2[BUF_SIZE];
	char* spacepos;
	char* newline;
	int buf1len;
	int buf2len;
	FILE *file1;
	FILE *file2;
	int val1;
	int val2;
	int totval;
	int namelen1;
	int namelen2;
	int pageid;
	char* retval;
	char* totstart;
	
	--argc; ++argv;
	if (argc != 2) {
		fprintf(stderr,"Usage: ./logmerge <inputfilename1> <inputfilename2>\n");
		fprintf(stderr,"\t<inputfile1> - Page link dump\n");
		fprintf(stderr,"\t<inputfile2> - NS0 title dump\n");
		return 1;
	}
	
	file1 = fopen(argv[0], "rb");
	if (file1 == 0) {fprintf(stderr, "fopen failed for %s\n", argv[0]); return 2;}
	file2 = fopen(argv[1], "rb");
	if (file2 == 0) {fclose(file1); fprintf(stderr, "fopen failed for %s\n", argv[1]); return 3;}
	
	fgets(buf1, BUF_SIZE, file1);
	newline = rawmemchr(buf1, '\n');
	*newline = 0;
	buf1len = (newline - buf1);
	fgets(buf2, BUF_SIZE, file2);
	newline = rawmemchr(buf2, '\n');
	*newline = 0;
	buf2len = (newline - buf2);
	enum actions action;
	
	while (! feof(file1)) {
		if (feof(file2)) action = WRITE_LEFT;
		else {
			namelen1 = (char *)rawmemchr(buf1, '\t') - buf1;
			namelen2 = buf2len;
			int maxlen = ((namelen1 < namelen2) ? namelen1 : namelen2);
			int diff = memcmp(buf1, buf2, maxlen);
			if (diff < 0) action = WRITE_LEFT;
			else if (diff > 0) action = WRITE_RIGHT;
			else {
				if (namelen1 < namelen2) action = WRITE_LEFT;
				else if (namelen1 > namelen2) action = WRITE_RIGHT;
				else action = MERGE_BOTH;
			}
		}
		
		switch (action) {
			case WRITE_LEFT: // These are redlinks
				fwrite(buf1, 1, buf1len, stdout);
				fwrite("\n", 1, 1, stdout);
				break;
				
			case WRITE_RIGHT:
//				fwrite(buf2, 1, buf2len, stdout);
//				fwrite("\n", 1, 1, stdout);
				break;
				
			case MERGE_BOTH:
//				totstart = buf1 + namelen1 + 1;
//				spacepos = (char *)rawmemchr(totstart, ' ');
//				*spacepos = 0;
//				pageid = atoi(spacepos + 1);
//				val1 = atoi(totstart);
//				val2 = atoi(buf2 + namelen2 + 1);
//				totval = val1 + val2;
//				fwrite(buf1, 1, namelen1, stdout);
//				fprintf(stdout, " %d %d\n", totval, pageid);
				break;
		}
		
		if (action == WRITE_LEFT || action == MERGE_BOTH) {
			retval = fgets(buf1, BUF_SIZE, file1);
			if (retval) {
				newline = rawmemchr(buf1, '\n');
				*newline = 0;
				buf1len = (newline - buf1);
			}
		}
	
		if (action == WRITE_RIGHT) { // No MERGE_BOTH, can be duplicate LEFTs
			retval = fgets(buf2, BUF_SIZE, file2);
			if (retval) {
				newline = rawmemchr(buf2, '\n');
				*newline = 0;
				buf2len = (newline - buf2);
			}
		}
		
	}
	
	fclose(file1);
	fclose(file2);
	return 0;
}
