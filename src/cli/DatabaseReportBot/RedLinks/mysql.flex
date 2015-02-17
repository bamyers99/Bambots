/*
	mysqldump to tsv flex filter
	
	To generate c code: flex -Cf -8 mysql.flex
	To compile c code: gcc lex.yy.c -lfl -o mysqlparse
	To run: ./mysqlparse <inputfile> <outputfile>
		<inputfile> - Mysqldump file to convert to tsv formatted rows
		<outputfile> - (Optional) tsv output file. If not specified, output files are written to the same directory as <inputfile> and are named using
			the mysql table name + .tsv
			A dash (-) means write to stdout

This file is made available under the Creative Commons CC0 1.0 Universal Public Domain Dedication.
The person who associated a work with this deed has dedicated the work to the public domain by waiving all of his or her rights to the work worldwide under copyright law, including all related and neighboring rights, to the extent allowed by law. You can copy, modify, distribute and perform the work, even for commercial purposes, all without asking permission.  http://creativecommons.org/publicdomain/zero/1.0/
*/


%top{
#define _FILE_OFFSET_BITS 64
}

%{
#include <stdio.h>
#include <string.h>
#define MAX_TABLENAME_LEN 128
#define MAX_PATH_LEN 2048
%}

	int row_count = 0;
	char prevtablename[MAX_TABLENAME_LEN] = {0};
	char tablename[MAX_TABLENAME_LEN] = {0};
	char pathprefix[MAX_PATH_LEN] = {0};
	char outputpath[MAX_PATH_LEN] = {0};
	enum output_types {OT_TABLENAME, OT_FILENAME, OT_STDOUT} output_type = OT_TABLENAME;

%x INTABLE
%x INROW
%x INSTRING

WS	[[:space:]]
TABLESTART	INSERT{WS}+INTO{WS}*`[^`]+`{WS}*VALUES{WS}*

%%

{TABLESTART}	{
		BEGIN(INTABLE);
		char* startPos = strchr(yytext, '`') + 1;
		char* endPos = strchr(startPos, '`');
		*endPos = 0;
		strncpy(tablename, startPos, MAX_TABLENAME_LEN);
		tablename[MAX_TABLENAME_LEN - 1] = 0;
		
		if (strcmp(tablename, prevtablename) != 0) {
			if (row_count > 0) {
				fprintf(stderr, " Row count = %d\n", row_count);
				row_count = 0;
				if (output_type == OT_TABLENAME) fclose(yyout);
			}
			
			strcpy(prevtablename, tablename);
			
			if (output_type == OT_TABLENAME) {
				strcpy(outputpath, pathprefix);
				outputpath[MAX_PATH_LEN - (strlen(tablename) + 5)] = 0;
				strcat(outputpath, tablename);
				strcat(outputpath, ".tsv");
				yyout = fopen(outputpath, "wb");
			}
			
			fprintf(stderr, "%s\n", tablename);
		}
	}

<INTABLE>{
	"("	{
		BEGIN(INROW);
		++row_count;
		if (row_count % 100000 == 0) fprintf(stderr, " Row count = %d\n", row_count);
		}
	,
	;	{
		BEGIN(INITIAL);
		}
	}

<INROW>{
	")"	{
		fprintf(yyout, "\n");
		BEGIN(INTABLE);
		}
	'	BEGIN(INSTRING);
	,	fprintf(yyout, "\t");
	.	ECHO;
	}

<INSTRING>{
	[^'"\\]+	ECHO;
	\\'		fprintf(yyout,"'");
	\\\"	fprintf(yyout,"\"");
	\\\\	fprintf(yyout,"\\");
	\\		ECHO;
	\"		ECHO;
	'		BEGIN(INROW);
	}

.
\n

%%

int main(int argc,char** argv){
	--argc; ++argv;
	if (argc == 0 || argc > 2) {
		fprintf(stderr,"Usage: ./mysqlparse <inputfilename> <outpufilename>\n");
		fprintf(stderr,"\t<inputfile> - Mysqldump file to convert to tsv formatted rows\n");
		fprintf(stderr,"\t<outputfile> - (Optional) tsv output file. If not specified, output files are written to the same directory as <inputfile> and are named using the mysql table name + .tsv\n");
		fprintf(stderr,"\t\tA dash (-) means write to stdout\n");
		return 1;
	}
	
//	yyin = fopen(argv[0], "rb");
//	if (yyin == 0) {fprintf(stderr, "fopen failed for %s\n", argv[0]); return 2;}
	
	if (argc == 2) {
		if (strcmp(argv[1], "-") == 0) output_type = OT_STDOUT;
		else {
			output_type = OT_FILENAME;
			yyout = fopen(argv[1], "wb");
			if (yyout == 0) {fprintf(stderr, "fopen failed for %s\n", argv[1]); return 3;}
		}
	} else {
		char* slashpos = strrchr(argv[0], '/');
		if (slashpos == 0) slashpos = strrchr(argv[0], '\\');
		if (slashpos > 0) {
			++slashpos;
			int n = slashpos - argv[0];
			if (n > MAX_PATH_LEN - 1) n = MAX_PATH_LEN - 1;
			memcpy(pathprefix, argv[0], n);
			pathprefix[n] = 0;
		}
	}
	
	yylex();
//	fclose(yyin);
	if (row_count > 0) fprintf(stderr, " Row count = %d\n", row_count);
}
