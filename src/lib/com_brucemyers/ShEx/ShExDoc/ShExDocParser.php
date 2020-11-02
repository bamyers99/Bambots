<?php

/*
 * Generated from ShExDoc.g4 by ANTLR 4.8
 */

namespace com_brucemyers\ShEx\ShExDoc {
	use Antlr\Antlr4\Runtime\Atn\ATN;
	use Antlr\Antlr4\Runtime\Atn\ATNDeserializer;
	use Antlr\Antlr4\Runtime\Atn\ParserATNSimulator;
	use Antlr\Antlr4\Runtime\Dfa\DFA;
	use Antlr\Antlr4\Runtime\Error\Exceptions\FailedPredicateException;
	use Antlr\Antlr4\Runtime\Error\Exceptions\NoViableAltException;
	use Antlr\Antlr4\Runtime\PredictionContexts\PredictionContextCache;
	use Antlr\Antlr4\Runtime\Error\Exceptions\RecognitionException;
	use Antlr\Antlr4\Runtime\RuleContext;
	use Antlr\Antlr4\Runtime\Token;
	use Antlr\Antlr4\Runtime\TokenStream;
	use Antlr\Antlr4\Runtime\Vocabulary;
	use Antlr\Antlr4\Runtime\VocabularyImpl;
	use Antlr\Antlr4\Runtime\RuntimeMetaData;
	use Antlr\Antlr4\Runtime\Parser;

	final class ShExDocParser extends Parser
	{
		public const T__0 = 1, T__1 = 2, T__2 = 3, T__3 = 4, T__4 = 5, T__5 = 6, 
               T__6 = 7, T__7 = 8, T__8 = 9, T__9 = 10, T__10 = 11, T__11 = 12, 
               T__12 = 13, T__13 = 14, T__14 = 15, T__15 = 16, T__16 = 17, 
               T__17 = 18, T__18 = 19, T__19 = 20, T__20 = 21, KW_ABSTRACT = 22, 
               KW_BASE = 23, KW_EXTENDS = 24, KW_IMPORT = 25, KW_RESTRICTS = 26, 
               KW_EXTERNAL = 27, KW_PREFIX = 28, KW_START = 29, KW_VIRTUAL = 30, 
               KW_CLOSED = 31, KW_EXTRA = 32, KW_LITERAL = 33, KW_IRI = 34, 
               KW_NONLITERAL = 35, KW_BNODE = 36, KW_AND = 37, KW_OR = 38, 
               KW_MININCLUSIVE = 39, KW_MINEXCLUSIVE = 40, KW_MAXINCLUSIVE = 41, 
               KW_MAXEXCLUSIVE = 42, KW_LENGTH = 43, KW_MINLENGTH = 44, 
               KW_MAXLENGTH = 45, KW_TOTALDIGITS = 46, KW_FRACTIONDIGITS = 47, 
               KW_NOT = 48, KW_TRUE = 49, KW_FALSE = 50, PASS = 51, COMMENT = 52, 
               CODE = 53, RDF_TYPE = 54, IRIREF = 55, PNAME_NS = 56, PNAME_LN = 57, 
               ATPNAME_NS = 58, ATPNAME_LN = 59, REGEXP = 60, REGEXP_FLAGS = 61, 
               BLANK_NODE_LABEL = 62, LANGTAG = 63, INTEGER = 64, DECIMAL = 65, 
               DOUBLE = 66, STEM_MARK = 67, UNBOUNDED = 68, STRING_LITERAL1 = 69, 
               STRING_LITERAL2 = 70, STRING_LITERAL_LONG1 = 71, STRING_LITERAL_LONG2 = 72;

		public const RULE_shExDoc = 0, RULE_directive = 1, RULE_baseDecl = 2, 
               RULE_prefixDecl = 3, RULE_importDecl = 4, RULE_notStartAction = 5, 
               RULE_start = 6, RULE_startActions = 7, RULE_statement = 8, 
               RULE_shapeExprDecl = 9, RULE_shapeExpression = 10, RULE_inlineShapeExpression = 11, 
               RULE_shapeOr = 12, RULE_inlineShapeOr = 13, RULE_shapeAnd = 14, 
               RULE_inlineShapeAnd = 15, RULE_shapeNot = 16, RULE_inlineShapeNot = 17, 
               RULE_shapeAtom = 18, RULE_inlineShapeAtom = 19, RULE_shapeOrRef = 20, 
               RULE_inlineShapeOrRef = 21, RULE_shapeRef = 22, RULE_inlineLitNodeConstraint = 23, 
               RULE_litNodeConstraint = 24, RULE_inlineNonLitNodeConstraint = 25, 
               RULE_nonLitNodeConstraint = 26, RULE_nonLiteralKind = 27, 
               RULE_xsFacet = 28, RULE_stringFacet = 29, RULE_stringLength = 30, 
               RULE_numericFacet = 31, RULE_numericRange = 32, RULE_numericLength = 33, 
               RULE_rawNumeric = 34, RULE_shapeDefinition = 35, RULE_inlineShapeDefinition = 36, 
               RULE_qualifier = 37, RULE_extraPropertySet = 38, RULE_tripleExpression = 39, 
               RULE_oneOfTripleExpr = 40, RULE_multiElementOneOf = 41, RULE_groupTripleExpr = 42, 
               RULE_singleElementGroup = 43, RULE_multiElementGroup = 44, 
               RULE_unaryTripleExpr = 45, RULE_bracketedTripleExpr = 46, 
               RULE_tripleConstraint = 47, RULE_cardinality = 48, RULE_repeatRange = 49, 
               RULE_senseFlags = 50, RULE_valueSet = 51, RULE_valueSetValue = 52, 
               RULE_iriRange = 53, RULE_iriExclusion = 54, RULE_literalRange = 55, 
               RULE_literalExclusion = 56, RULE_languageRange = 57, RULE_languageExclusion = 58, 
               RULE_r_include = 59, RULE_annotation = 60, RULE_semanticAction = 61, 
               RULE_literal = 62, RULE_predicate = 63, RULE_rdfType = 64, 
               RULE_datatype = 65, RULE_shapeExprLabel = 66, RULE_tripleExprLabel = 67, 
               RULE_numericLiteral = 68, RULE_rdfLiteral = 69, RULE_booleanLiteral = 70, 
               RULE_string = 71, RULE_iri = 72, RULE_prefixedName = 73, 
               RULE_blankNode = 74, RULE_extension = 75, RULE_restrictions = 76;

		/**
		 * @var array<string>
		 */
		public const RULE_NAMES = [
			'shExDoc', 'directive', 'baseDecl', 'prefixDecl', 'importDecl', 'notStartAction', 
			'start', 'startActions', 'statement', 'shapeExprDecl', 'shapeExpression', 
			'inlineShapeExpression', 'shapeOr', 'inlineShapeOr', 'shapeAnd', 'inlineShapeAnd', 
			'shapeNot', 'inlineShapeNot', 'shapeAtom', 'inlineShapeAtom', 'shapeOrRef', 
			'inlineShapeOrRef', 'shapeRef', 'inlineLitNodeConstraint', 'litNodeConstraint', 
			'inlineNonLitNodeConstraint', 'nonLitNodeConstraint', 'nonLiteralKind', 
			'xsFacet', 'stringFacet', 'stringLength', 'numericFacet', 'numericRange', 
			'numericLength', 'rawNumeric', 'shapeDefinition', 'inlineShapeDefinition', 
			'qualifier', 'extraPropertySet', 'tripleExpression', 'oneOfTripleExpr', 
			'multiElementOneOf', 'groupTripleExpr', 'singleElementGroup', 'multiElementGroup', 
			'unaryTripleExpr', 'bracketedTripleExpr', 'tripleConstraint', 'cardinality', 
			'repeatRange', 'senseFlags', 'valueSet', 'valueSetValue', 'iriRange', 
			'iriExclusion', 'literalRange', 'literalExclusion', 'languageRange', 
			'languageExclusion', 'r_include', 'annotation', 'semanticAction', 'literal', 
			'predicate', 'rdfType', 'datatype', 'shapeExprLabel', 'tripleExprLabel', 
			'numericLiteral', 'rdfLiteral', 'booleanLiteral', 'string', 'iri', 'prefixedName', 
			'blankNode', 'extension', 'restrictions'
		];

		/**
		 * @var array<string|null>
		 */
		private const LITERAL_NAMES = [
		    null, "'='", "'('", "')'", "'.'", "'@'", "'{'", "'}'", "'|'", "';'", 
		    "'$'", "'+'", "'?'", "','", "'^'", "'['", "']'", "'-'", "'&'", "'//'", 
		    "'%'", "'^^'", null, null, null, null, null, null, null, null, null, 
		    null, null, null, null, null, null, null, null, null, null, null, 
		    null, null, null, null, null, null, null, "'true'", "'false'", null, 
		    null, null, "'a'", null, null, null, null, null, null, null, null, 
		    null, null, null, null, "'~'", "'*'"
		];

		/**
		 * @var array<string>
		 */
		private const SYMBOLIC_NAMES = [
		    null, null, null, null, null, null, null, null, null, null, null, 
		    null, null, null, null, null, null, null, null, null, null, null, 
		    "KW_ABSTRACT", "KW_BASE", "KW_EXTENDS", "KW_IMPORT", "KW_RESTRICTS", 
		    "KW_EXTERNAL", "KW_PREFIX", "KW_START", "KW_VIRTUAL", "KW_CLOSED", 
		    "KW_EXTRA", "KW_LITERAL", "KW_IRI", "KW_NONLITERAL", "KW_BNODE", "KW_AND", 
		    "KW_OR", "KW_MININCLUSIVE", "KW_MINEXCLUSIVE", "KW_MAXINCLUSIVE", 
		    "KW_MAXEXCLUSIVE", "KW_LENGTH", "KW_MINLENGTH", "KW_MAXLENGTH", "KW_TOTALDIGITS", 
		    "KW_FRACTIONDIGITS", "KW_NOT", "KW_TRUE", "KW_FALSE", "PASS", "COMMENT", 
		    "CODE", "RDF_TYPE", "IRIREF", "PNAME_NS", "PNAME_LN", "ATPNAME_NS", 
		    "ATPNAME_LN", "REGEXP", "REGEXP_FLAGS", "BLANK_NODE_LABEL", "LANGTAG", 
		    "INTEGER", "DECIMAL", "DOUBLE", "STEM_MARK", "UNBOUNDED", "STRING_LITERAL1", 
		    "STRING_LITERAL2", "STRING_LITERAL_LONG1", "STRING_LITERAL_LONG2"
		];

		/**
		 * @var string
		 */
		private const SERIALIZED_ATN =
			"\u{3}\u{608B}\u{A72A}\u{8133}\u{B9ED}\u{417C}\u{3BE7}\u{7786}\u{5964}" .
		    "\u{3}\u{4A}\u{2C6}\u{4}\u{2}\u{9}\u{2}\u{4}\u{3}\u{9}\u{3}\u{4}\u{4}" .
		    "\u{9}\u{4}\u{4}\u{5}\u{9}\u{5}\u{4}\u{6}\u{9}\u{6}\u{4}\u{7}\u{9}" .
		    "\u{7}\u{4}\u{8}\u{9}\u{8}\u{4}\u{9}\u{9}\u{9}\u{4}\u{A}\u{9}\u{A}" .
		    "\u{4}\u{B}\u{9}\u{B}\u{4}\u{C}\u{9}\u{C}\u{4}\u{D}\u{9}\u{D}\u{4}" .
		    "\u{E}\u{9}\u{E}\u{4}\u{F}\u{9}\u{F}\u{4}\u{10}\u{9}\u{10}\u{4}\u{11}" .
		    "\u{9}\u{11}\u{4}\u{12}\u{9}\u{12}\u{4}\u{13}\u{9}\u{13}\u{4}\u{14}" .
		    "\u{9}\u{14}\u{4}\u{15}\u{9}\u{15}\u{4}\u{16}\u{9}\u{16}\u{4}\u{17}" .
		    "\u{9}\u{17}\u{4}\u{18}\u{9}\u{18}\u{4}\u{19}\u{9}\u{19}\u{4}\u{1A}" .
		    "\u{9}\u{1A}\u{4}\u{1B}\u{9}\u{1B}\u{4}\u{1C}\u{9}\u{1C}\u{4}\u{1D}" .
		    "\u{9}\u{1D}\u{4}\u{1E}\u{9}\u{1E}\u{4}\u{1F}\u{9}\u{1F}\u{4}\u{20}" .
		    "\u{9}\u{20}\u{4}\u{21}\u{9}\u{21}\u{4}\u{22}\u{9}\u{22}\u{4}\u{23}" .
		    "\u{9}\u{23}\u{4}\u{24}\u{9}\u{24}\u{4}\u{25}\u{9}\u{25}\u{4}\u{26}" .
		    "\u{9}\u{26}\u{4}\u{27}\u{9}\u{27}\u{4}\u{28}\u{9}\u{28}\u{4}\u{29}" .
		    "\u{9}\u{29}\u{4}\u{2A}\u{9}\u{2A}\u{4}\u{2B}\u{9}\u{2B}\u{4}\u{2C}" .
		    "\u{9}\u{2C}\u{4}\u{2D}\u{9}\u{2D}\u{4}\u{2E}\u{9}\u{2E}\u{4}\u{2F}" .
		    "\u{9}\u{2F}\u{4}\u{30}\u{9}\u{30}\u{4}\u{31}\u{9}\u{31}\u{4}\u{32}" .
		    "\u{9}\u{32}\u{4}\u{33}\u{9}\u{33}\u{4}\u{34}\u{9}\u{34}\u{4}\u{35}" .
		    "\u{9}\u{35}\u{4}\u{36}\u{9}\u{36}\u{4}\u{37}\u{9}\u{37}\u{4}\u{38}" .
		    "\u{9}\u{38}\u{4}\u{39}\u{9}\u{39}\u{4}\u{3A}\u{9}\u{3A}\u{4}\u{3B}" .
		    "\u{9}\u{3B}\u{4}\u{3C}\u{9}\u{3C}\u{4}\u{3D}\u{9}\u{3D}\u{4}\u{3E}" .
		    "\u{9}\u{3E}\u{4}\u{3F}\u{9}\u{3F}\u{4}\u{40}\u{9}\u{40}\u{4}\u{41}" .
		    "\u{9}\u{41}\u{4}\u{42}\u{9}\u{42}\u{4}\u{43}\u{9}\u{43}\u{4}\u{44}" .
		    "\u{9}\u{44}\u{4}\u{45}\u{9}\u{45}\u{4}\u{46}\u{9}\u{46}\u{4}\u{47}" .
		    "\u{9}\u{47}\u{4}\u{48}\u{9}\u{48}\u{4}\u{49}\u{9}\u{49}\u{4}\u{4A}" .
		    "\u{9}\u{4A}\u{4}\u{4B}\u{9}\u{4B}\u{4}\u{4C}\u{9}\u{4C}\u{4}\u{4D}" .
		    "\u{9}\u{4D}\u{4}\u{4E}\u{9}\u{4E}\u{3}\u{2}\u{7}\u{2}\u{9E}\u{A}\u{2}" .
		    "\u{C}\u{2}\u{E}\u{2}\u{A1}\u{B}\u{2}\u{3}\u{2}\u{3}\u{2}\u{5}\u{2}" .
		    "\u{A5}\u{A}\u{2}\u{3}\u{2}\u{7}\u{2}\u{A8}\u{A}\u{2}\u{C}\u{2}\u{E}" .
		    "\u{2}\u{AB}\u{B}\u{2}\u{5}\u{2}\u{AD}\u{A}\u{2}\u{3}\u{2}\u{3}\u{2}" .
		    "\u{3}\u{3}\u{3}\u{3}\u{3}\u{3}\u{5}\u{3}\u{B4}\u{A}\u{3}\u{3}\u{4}" .
		    "\u{3}\u{4}\u{3}\u{4}\u{3}\u{5}\u{3}\u{5}\u{3}\u{5}\u{3}\u{5}\u{3}" .
		    "\u{6}\u{3}\u{6}\u{3}\u{6}\u{3}\u{7}\u{3}\u{7}\u{5}\u{7}\u{C2}\u{A}" .
		    "\u{7}\u{3}\u{8}\u{3}\u{8}\u{3}\u{8}\u{3}\u{8}\u{3}\u{9}\u{6}\u{9}" .
		    "\u{C9}\u{A}\u{9}\u{D}\u{9}\u{E}\u{9}\u{CA}\u{3}\u{A}\u{3}\u{A}\u{5}" .
		    "\u{A}\u{CF}\u{A}\u{A}\u{3}\u{B}\u{5}\u{B}\u{D2}\u{A}\u{B}\u{3}\u{B}" .
		    "\u{3}\u{B}\u{7}\u{B}\u{D6}\u{A}\u{B}\u{C}\u{B}\u{E}\u{B}\u{D9}\u{B}" .
		    "\u{B}\u{3}\u{B}\u{3}\u{B}\u{5}\u{B}\u{DD}\u{A}\u{B}\u{3}\u{C}\u{3}" .
		    "\u{C}\u{3}\u{D}\u{3}\u{D}\u{3}\u{E}\u{3}\u{E}\u{3}\u{E}\u{7}\u{E}" .
		    "\u{E6}\u{A}\u{E}\u{C}\u{E}\u{E}\u{E}\u{E9}\u{B}\u{E}\u{3}\u{F}\u{3}" .
		    "\u{F}\u{3}\u{F}\u{7}\u{F}\u{EE}\u{A}\u{F}\u{C}\u{F}\u{E}\u{F}\u{F1}" .
		    "\u{B}\u{F}\u{3}\u{10}\u{3}\u{10}\u{3}\u{10}\u{7}\u{10}\u{F6}\u{A}" .
		    "\u{10}\u{C}\u{10}\u{E}\u{10}\u{F9}\u{B}\u{10}\u{3}\u{11}\u{3}\u{11}" .
		    "\u{3}\u{11}\u{7}\u{11}\u{FE}\u{A}\u{11}\u{C}\u{11}\u{E}\u{11}\u{101}" .
		    "\u{B}\u{11}\u{3}\u{12}\u{5}\u{12}\u{104}\u{A}\u{12}\u{3}\u{12}\u{3}" .
		    "\u{12}\u{3}\u{13}\u{5}\u{13}\u{109}\u{A}\u{13}\u{3}\u{13}\u{3}\u{13}" .
		    "\u{3}\u{14}\u{3}\u{14}\u{5}\u{14}\u{10F}\u{A}\u{14}\u{3}\u{14}\u{3}" .
		    "\u{14}\u{3}\u{14}\u{5}\u{14}\u{114}\u{A}\u{14}\u{3}\u{14}\u{3}\u{14}" .
		    "\u{3}\u{14}\u{3}\u{14}\u{3}\u{14}\u{5}\u{14}\u{11B}\u{A}\u{14}\u{3}" .
		    "\u{15}\u{3}\u{15}\u{5}\u{15}\u{11F}\u{A}\u{15}\u{3}\u{15}\u{3}\u{15}" .
		    "\u{3}\u{15}\u{5}\u{15}\u{124}\u{A}\u{15}\u{3}\u{15}\u{3}\u{15}\u{3}" .
		    "\u{15}\u{3}\u{15}\u{3}\u{15}\u{5}\u{15}\u{12B}\u{A}\u{15}\u{3}\u{16}" .
		    "\u{3}\u{16}\u{5}\u{16}\u{12F}\u{A}\u{16}\u{3}\u{17}\u{3}\u{17}\u{5}" .
		    "\u{17}\u{133}\u{A}\u{17}\u{3}\u{18}\u{3}\u{18}\u{3}\u{18}\u{3}\u{18}" .
		    "\u{5}\u{18}\u{139}\u{A}\u{18}\u{3}\u{19}\u{3}\u{19}\u{7}\u{19}\u{13D}" .
		    "\u{A}\u{19}\u{C}\u{19}\u{E}\u{19}\u{140}\u{B}\u{19}\u{3}\u{19}\u{3}" .
		    "\u{19}\u{7}\u{19}\u{144}\u{A}\u{19}\u{C}\u{19}\u{E}\u{19}\u{147}\u{B}" .
		    "\u{19}\u{3}\u{19}\u{3}\u{19}\u{7}\u{19}\u{14B}\u{A}\u{19}\u{C}\u{19}" .
		    "\u{E}\u{19}\u{14E}\u{B}\u{19}\u{3}\u{19}\u{3}\u{19}\u{7}\u{19}\u{152}" .
		    "\u{A}\u{19}\u{C}\u{19}\u{E}\u{19}\u{155}\u{B}\u{19}\u{3}\u{19}\u{6}" .
		    "\u{19}\u{158}\u{A}\u{19}\u{D}\u{19}\u{E}\u{19}\u{159}\u{5}\u{19}\u{15C}" .
		    "\u{A}\u{19}\u{3}\u{1A}\u{3}\u{1A}\u{7}\u{1A}\u{160}\u{A}\u{1A}\u{C}" .
		    "\u{1A}\u{E}\u{1A}\u{163}\u{B}\u{1A}\u{3}\u{1A}\u{7}\u{1A}\u{166}\u{A}" .
		    "\u{1A}\u{C}\u{1A}\u{E}\u{1A}\u{169}\u{B}\u{1A}\u{3}\u{1B}\u{3}\u{1B}" .
		    "\u{7}\u{1B}\u{16D}\u{A}\u{1B}\u{C}\u{1B}\u{E}\u{1B}\u{170}\u{B}\u{1B}" .
		    "\u{3}\u{1B}\u{6}\u{1B}\u{173}\u{A}\u{1B}\u{D}\u{1B}\u{E}\u{1B}\u{174}" .
		    "\u{5}\u{1B}\u{177}\u{A}\u{1B}\u{3}\u{1C}\u{3}\u{1C}\u{7}\u{1C}\u{17B}" .
		    "\u{A}\u{1C}\u{C}\u{1C}\u{E}\u{1C}\u{17E}\u{B}\u{1C}\u{3}\u{1C}\u{7}" .
		    "\u{1C}\u{181}\u{A}\u{1C}\u{C}\u{1C}\u{E}\u{1C}\u{184}\u{B}\u{1C}\u{3}" .
		    "\u{1D}\u{3}\u{1D}\u{3}\u{1E}\u{3}\u{1E}\u{5}\u{1E}\u{18A}\u{A}\u{1E}" .
		    "\u{3}\u{1F}\u{3}\u{1F}\u{3}\u{1F}\u{3}\u{1F}\u{3}\u{1F}\u{5}\u{1F}" .
		    "\u{191}\u{A}\u{1F}\u{5}\u{1F}\u{193}\u{A}\u{1F}\u{3}\u{20}\u{3}\u{20}" .
		    "\u{3}\u{21}\u{3}\u{21}\u{3}\u{21}\u{3}\u{21}\u{3}\u{21}\u{3}\u{21}" .
		    "\u{5}\u{21}\u{19D}\u{A}\u{21}\u{3}\u{22}\u{3}\u{22}\u{3}\u{23}\u{3}" .
		    "\u{23}\u{3}\u{24}\u{3}\u{24}\u{3}\u{25}\u{3}\u{25}\u{7}\u{25}\u{1A7}" .
		    "\u{A}\u{25}\u{C}\u{25}\u{E}\u{25}\u{1AA}\u{B}\u{25}\u{3}\u{25}\u{7}" .
		    "\u{25}\u{1AD}\u{A}\u{25}\u{C}\u{25}\u{E}\u{25}\u{1B0}\u{B}\u{25}\u{3}" .
		    "\u{26}\u{7}\u{26}\u{1B3}\u{A}\u{26}\u{C}\u{26}\u{E}\u{26}\u{1B6}\u{B}" .
		    "\u{26}\u{3}\u{26}\u{3}\u{26}\u{5}\u{26}\u{1BA}\u{A}\u{26}\u{3}\u{26}" .
		    "\u{3}\u{26}\u{3}\u{27}\u{3}\u{27}\u{3}\u{27}\u{5}\u{27}\u{1C1}\u{A}" .
		    "\u{27}\u{3}\u{28}\u{3}\u{28}\u{6}\u{28}\u{1C5}\u{A}\u{28}\u{D}\u{28}" .
		    "\u{E}\u{28}\u{1C6}\u{3}\u{29}\u{3}\u{29}\u{3}\u{2A}\u{3}\u{2A}\u{5}" .
		    "\u{2A}\u{1CD}\u{A}\u{2A}\u{3}\u{2B}\u{3}\u{2B}\u{3}\u{2B}\u{6}\u{2B}" .
		    "\u{1D2}\u{A}\u{2B}\u{D}\u{2B}\u{E}\u{2B}\u{1D3}\u{3}\u{2C}\u{3}\u{2C}" .
		    "\u{5}\u{2C}\u{1D8}\u{A}\u{2C}\u{3}\u{2D}\u{3}\u{2D}\u{5}\u{2D}\u{1DC}" .
		    "\u{A}\u{2D}\u{3}\u{2E}\u{3}\u{2E}\u{3}\u{2E}\u{6}\u{2E}\u{1E1}\u{A}" .
		    "\u{2E}\u{D}\u{2E}\u{E}\u{2E}\u{1E2}\u{3}\u{2E}\u{5}\u{2E}\u{1E6}\u{A}" .
		    "\u{2E}\u{3}\u{2F}\u{3}\u{2F}\u{5}\u{2F}\u{1EA}\u{A}\u{2F}\u{3}\u{2F}" .
		    "\u{3}\u{2F}\u{5}\u{2F}\u{1EE}\u{A}\u{2F}\u{3}\u{2F}\u{5}\u{2F}\u{1F1}" .
		    "\u{A}\u{2F}\u{3}\u{30}\u{3}\u{30}\u{3}\u{30}\u{3}\u{30}\u{5}\u{30}" .
		    "\u{1F7}\u{A}\u{30}\u{3}\u{30}\u{7}\u{30}\u{1FA}\u{A}\u{30}\u{C}\u{30}" .
		    "\u{E}\u{30}\u{1FD}\u{B}\u{30}\u{3}\u{30}\u{7}\u{30}\u{200}\u{A}\u{30}" .
		    "\u{C}\u{30}\u{E}\u{30}\u{203}\u{B}\u{30}\u{3}\u{31}\u{5}\u{31}\u{206}" .
		    "\u{A}\u{31}\u{3}\u{31}\u{3}\u{31}\u{3}\u{31}\u{5}\u{31}\u{20B}\u{A}" .
		    "\u{31}\u{3}\u{31}\u{7}\u{31}\u{20E}\u{A}\u{31}\u{C}\u{31}\u{E}\u{31}" .
		    "\u{211}\u{B}\u{31}\u{3}\u{31}\u{7}\u{31}\u{214}\u{A}\u{31}\u{C}\u{31}" .
		    "\u{E}\u{31}\u{217}\u{B}\u{31}\u{3}\u{32}\u{3}\u{32}\u{3}\u{32}\u{3}" .
		    "\u{32}\u{5}\u{32}\u{21D}\u{A}\u{32}\u{3}\u{33}\u{3}\u{33}\u{3}\u{33}" .
		    "\u{3}\u{33}\u{3}\u{33}\u{3}\u{33}\u{3}\u{33}\u{5}\u{33}\u{226}\u{A}" .
		    "\u{33}\u{3}\u{33}\u{5}\u{33}\u{229}\u{A}\u{33}\u{3}\u{34}\u{3}\u{34}" .
		    "\u{3}\u{35}\u{3}\u{35}\u{7}\u{35}\u{22F}\u{A}\u{35}\u{C}\u{35}\u{E}" .
		    "\u{35}\u{232}\u{B}\u{35}\u{3}\u{35}\u{3}\u{35}\u{3}\u{36}\u{3}\u{36}" .
		    "\u{3}\u{36}\u{3}\u{36}\u{3}\u{36}\u{6}\u{36}\u{23B}\u{A}\u{36}\u{D}" .
		    "\u{36}\u{E}\u{36}\u{23C}\u{3}\u{36}\u{6}\u{36}\u{240}\u{A}\u{36}\u{D}" .
		    "\u{36}\u{E}\u{36}\u{241}\u{3}\u{36}\u{6}\u{36}\u{245}\u{A}\u{36}\u{D}" .
		    "\u{36}\u{E}\u{36}\u{246}\u{5}\u{36}\u{249}\u{A}\u{36}\u{5}\u{36}\u{24B}" .
		    "\u{A}\u{36}\u{3}\u{37}\u{3}\u{37}\u{3}\u{37}\u{7}\u{37}\u{250}\u{A}" .
		    "\u{37}\u{C}\u{37}\u{E}\u{37}\u{253}\u{B}\u{37}\u{5}\u{37}\u{255}\u{A}" .
		    "\u{37}\u{3}\u{38}\u{3}\u{38}\u{3}\u{38}\u{5}\u{38}\u{25A}\u{A}\u{38}" .
		    "\u{3}\u{39}\u{3}\u{39}\u{3}\u{39}\u{7}\u{39}\u{25F}\u{A}\u{39}\u{C}" .
		    "\u{39}\u{E}\u{39}\u{262}\u{B}\u{39}\u{5}\u{39}\u{264}\u{A}\u{39}\u{3}" .
		    "\u{3A}\u{3}\u{3A}\u{3}\u{3A}\u{5}\u{3A}\u{269}\u{A}\u{3A}\u{3}\u{3B}" .
		    "\u{3}\u{3B}\u{3}\u{3B}\u{7}\u{3B}\u{26E}\u{A}\u{3B}\u{C}\u{3B}\u{E}" .
		    "\u{3B}\u{271}\u{B}\u{3B}\u{5}\u{3B}\u{273}\u{A}\u{3B}\u{3}\u{3B}\u{3}" .
		    "\u{3B}\u{3}\u{3B}\u{7}\u{3B}\u{278}\u{A}\u{3B}\u{C}\u{3B}\u{E}\u{3B}" .
		    "\u{27B}\u{B}\u{3B}\u{5}\u{3B}\u{27D}\u{A}\u{3B}\u{3}\u{3C}\u{3}\u{3C}" .
		    "\u{3}\u{3C}\u{5}\u{3C}\u{282}\u{A}\u{3C}\u{3}\u{3D}\u{3}\u{3D}\u{3}" .
		    "\u{3D}\u{3}\u{3E}\u{3}\u{3E}\u{3}\u{3E}\u{3}\u{3E}\u{5}\u{3E}\u{28B}" .
		    "\u{A}\u{3E}\u{3}\u{3F}\u{3}\u{3F}\u{3}\u{3F}\u{3}\u{3F}\u{3}\u{40}" .
		    "\u{3}\u{40}\u{3}\u{40}\u{5}\u{40}\u{294}\u{A}\u{40}\u{3}\u{41}\u{3}" .
		    "\u{41}\u{5}\u{41}\u{298}\u{A}\u{41}\u{3}\u{42}\u{3}\u{42}\u{3}\u{43}" .
		    "\u{3}\u{43}\u{3}\u{44}\u{3}\u{44}\u{5}\u{44}\u{2A0}\u{A}\u{44}\u{3}" .
		    "\u{45}\u{3}\u{45}\u{5}\u{45}\u{2A4}\u{A}\u{45}\u{3}\u{46}\u{3}\u{46}" .
		    "\u{3}\u{47}\u{3}\u{47}\u{3}\u{47}\u{3}\u{47}\u{5}\u{47}\u{2AC}\u{A}" .
		    "\u{47}\u{3}\u{48}\u{3}\u{48}\u{3}\u{49}\u{3}\u{49}\u{3}\u{4A}\u{3}" .
		    "\u{4A}\u{5}\u{4A}\u{2B4}\u{A}\u{4A}\u{3}\u{4B}\u{3}\u{4B}\u{3}\u{4C}" .
		    "\u{3}\u{4C}\u{3}\u{4D}\u{3}\u{4D}\u{3}\u{4D}\u{3}\u{4D}\u{5}\u{4D}" .
		    "\u{2BE}\u{A}\u{4D}\u{3}\u{4E}\u{3}\u{4E}\u{3}\u{4E}\u{3}\u{4E}\u{5}" .
		    "\u{4E}\u{2C4}\u{A}\u{4E}\u{3}\u{4E}\u{2}\u{2}\u{4F}\u{2}\u{4}\u{6}" .
		    "\u{8}\u{A}\u{C}\u{E}\u{10}\u{12}\u{14}\u{16}\u{18}\u{1A}\u{1C}\u{1E}" .
		    "\u{20}\u{22}\u{24}\u{26}\u{28}\u{2A}\u{2C}\u{2E}\u{30}\u{32}\u{34}" .
		    "\u{36}\u{38}\u{3A}\u{3C}\u{3E}\u{40}\u{42}\u{44}\u{46}\u{48}\u{4A}" .
		    "\u{4C}\u{4E}\u{50}\u{52}\u{54}\u{56}\u{58}\u{5A}\u{5C}\u{5E}\u{60}" .
		    "\u{62}\u{64}\u{66}\u{68}\u{6A}\u{6C}\u{6E}\u{70}\u{72}\u{74}\u{76}" .
		    "\u{78}\u{7A}\u{7C}\u{7E}\u{80}\u{82}\u{84}\u{86}\u{88}\u{8A}\u{8C}" .
		    "\u{8E}\u{90}\u{92}\u{94}\u{96}\u{98}\u{9A}\u{2}\u{C}\u{3}\u{2}\u{24}" .
		    "\u{26}\u{3}\u{2}\u{2D}\u{2F}\u{3}\u{2}\u{29}\u{2C}\u{3}\u{2}\u{30}" .
		    "\u{31}\u{3}\u{2}\u{42}\u{44}\u{4}\u{2}\u{42}\u{42}\u{46}\u{46}\u{4}" .
		    "\u{2}\u{16}\u{16}\u{37}\u{37}\u{3}\u{2}\u{33}\u{34}\u{3}\u{2}\u{47}" .
		    "\u{4A}\u{3}\u{2}\u{3A}\u{3B}\u{2}\u{2E9}\u{2}\u{9F}\u{3}\u{2}\u{2}" .
		    "\u{2}\u{4}\u{B3}\u{3}\u{2}\u{2}\u{2}\u{6}\u{B5}\u{3}\u{2}\u{2}\u{2}" .
		    "\u{8}\u{B8}\u{3}\u{2}\u{2}\u{2}\u{A}\u{BC}\u{3}\u{2}\u{2}\u{2}\u{C}" .
		    "\u{C1}\u{3}\u{2}\u{2}\u{2}\u{E}\u{C3}\u{3}\u{2}\u{2}\u{2}\u{10}\u{C8}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{12}\u{CE}\u{3}\u{2}\u{2}\u{2}\u{14}\u{D1}\u{3}" .
		    "\u{2}\u{2}\u{2}\u{16}\u{DE}\u{3}\u{2}\u{2}\u{2}\u{18}\u{E0}\u{3}\u{2}" .
		    "\u{2}\u{2}\u{1A}\u{E2}\u{3}\u{2}\u{2}\u{2}\u{1C}\u{EA}\u{3}\u{2}\u{2}" .
		    "\u{2}\u{1E}\u{F2}\u{3}\u{2}\u{2}\u{2}\u{20}\u{FA}\u{3}\u{2}\u{2}\u{2}" .
		    "\u{22}\u{103}\u{3}\u{2}\u{2}\u{2}\u{24}\u{108}\u{3}\u{2}\u{2}\u{2}" .
		    "\u{26}\u{11A}\u{3}\u{2}\u{2}\u{2}\u{28}\u{12A}\u{3}\u{2}\u{2}\u{2}" .
		    "\u{2A}\u{12E}\u{3}\u{2}\u{2}\u{2}\u{2C}\u{132}\u{3}\u{2}\u{2}\u{2}" .
		    "\u{2E}\u{138}\u{3}\u{2}\u{2}\u{2}\u{30}\u{15B}\u{3}\u{2}\u{2}\u{2}" .
		    "\u{32}\u{15D}\u{3}\u{2}\u{2}\u{2}\u{34}\u{176}\u{3}\u{2}\u{2}\u{2}" .
		    "\u{36}\u{178}\u{3}\u{2}\u{2}\u{2}\u{38}\u{185}\u{3}\u{2}\u{2}\u{2}" .
		    "\u{3A}\u{189}\u{3}\u{2}\u{2}\u{2}\u{3C}\u{192}\u{3}\u{2}\u{2}\u{2}" .
		    "\u{3E}\u{194}\u{3}\u{2}\u{2}\u{2}\u{40}\u{19C}\u{3}\u{2}\u{2}\u{2}" .
		    "\u{42}\u{19E}\u{3}\u{2}\u{2}\u{2}\u{44}\u{1A0}\u{3}\u{2}\u{2}\u{2}" .
		    "\u{46}\u{1A2}\u{3}\u{2}\u{2}\u{2}\u{48}\u{1A4}\u{3}\u{2}\u{2}\u{2}" .
		    "\u{4A}\u{1B4}\u{3}\u{2}\u{2}\u{2}\u{4C}\u{1C0}\u{3}\u{2}\u{2}\u{2}" .
		    "\u{4E}\u{1C2}\u{3}\u{2}\u{2}\u{2}\u{50}\u{1C8}\u{3}\u{2}\u{2}\u{2}" .
		    "\u{52}\u{1CC}\u{3}\u{2}\u{2}\u{2}\u{54}\u{1CE}\u{3}\u{2}\u{2}\u{2}" .
		    "\u{56}\u{1D7}\u{3}\u{2}\u{2}\u{2}\u{58}\u{1D9}\u{3}\u{2}\u{2}\u{2}" .
		    "\u{5A}\u{1DD}\u{3}\u{2}\u{2}\u{2}\u{5C}\u{1F0}\u{3}\u{2}\u{2}\u{2}" .
		    "\u{5E}\u{1F2}\u{3}\u{2}\u{2}\u{2}\u{60}\u{205}\u{3}\u{2}\u{2}\u{2}" .
		    "\u{62}\u{21C}\u{3}\u{2}\u{2}\u{2}\u{64}\u{228}\u{3}\u{2}\u{2}\u{2}" .
		    "\u{66}\u{22A}\u{3}\u{2}\u{2}\u{2}\u{68}\u{22C}\u{3}\u{2}\u{2}\u{2}" .
		    "\u{6A}\u{24A}\u{3}\u{2}\u{2}\u{2}\u{6C}\u{24C}\u{3}\u{2}\u{2}\u{2}" .
		    "\u{6E}\u{256}\u{3}\u{2}\u{2}\u{2}\u{70}\u{25B}\u{3}\u{2}\u{2}\u{2}" .
		    "\u{72}\u{265}\u{3}\u{2}\u{2}\u{2}\u{74}\u{27C}\u{3}\u{2}\u{2}\u{2}" .
		    "\u{76}\u{27E}\u{3}\u{2}\u{2}\u{2}\u{78}\u{283}\u{3}\u{2}\u{2}\u{2}" .
		    "\u{7A}\u{286}\u{3}\u{2}\u{2}\u{2}\u{7C}\u{28C}\u{3}\u{2}\u{2}\u{2}" .
		    "\u{7E}\u{293}\u{3}\u{2}\u{2}\u{2}\u{80}\u{297}\u{3}\u{2}\u{2}\u{2}" .
		    "\u{82}\u{299}\u{3}\u{2}\u{2}\u{2}\u{84}\u{29B}\u{3}\u{2}\u{2}\u{2}" .
		    "\u{86}\u{29F}\u{3}\u{2}\u{2}\u{2}\u{88}\u{2A3}\u{3}\u{2}\u{2}\u{2}" .
		    "\u{8A}\u{2A5}\u{3}\u{2}\u{2}\u{2}\u{8C}\u{2A7}\u{3}\u{2}\u{2}\u{2}" .
		    "\u{8E}\u{2AD}\u{3}\u{2}\u{2}\u{2}\u{90}\u{2AF}\u{3}\u{2}\u{2}\u{2}" .
		    "\u{92}\u{2B3}\u{3}\u{2}\u{2}\u{2}\u{94}\u{2B5}\u{3}\u{2}\u{2}\u{2}" .
		    "\u{96}\u{2B7}\u{3}\u{2}\u{2}\u{2}\u{98}\u{2BD}\u{3}\u{2}\u{2}\u{2}" .
		    "\u{9A}\u{2C3}\u{3}\u{2}\u{2}\u{2}\u{9C}\u{9E}\u{5}\u{4}\u{3}\u{2}" .
		    "\u{9D}\u{9C}\u{3}\u{2}\u{2}\u{2}\u{9E}\u{A1}\u{3}\u{2}\u{2}\u{2}\u{9F}" .
		    "\u{9D}\u{3}\u{2}\u{2}\u{2}\u{9F}\u{A0}\u{3}\u{2}\u{2}\u{2}\u{A0}\u{AC}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{A1}\u{9F}\u{3}\u{2}\u{2}\u{2}\u{A2}\u{A5}\u{5}" .
		    "\u{C}\u{7}\u{2}\u{A3}\u{A5}\u{5}\u{10}\u{9}\u{2}\u{A4}\u{A2}\u{3}" .
		    "\u{2}\u{2}\u{2}\u{A4}\u{A3}\u{3}\u{2}\u{2}\u{2}\u{A5}\u{A9}\u{3}\u{2}" .
		    "\u{2}\u{2}\u{A6}\u{A8}\u{5}\u{12}\u{A}\u{2}\u{A7}\u{A6}\u{3}\u{2}" .
		    "\u{2}\u{2}\u{A8}\u{AB}\u{3}\u{2}\u{2}\u{2}\u{A9}\u{A7}\u{3}\u{2}\u{2}" .
		    "\u{2}\u{A9}\u{AA}\u{3}\u{2}\u{2}\u{2}\u{AA}\u{AD}\u{3}\u{2}\u{2}\u{2}" .
		    "\u{AB}\u{A9}\u{3}\u{2}\u{2}\u{2}\u{AC}\u{A4}\u{3}\u{2}\u{2}\u{2}\u{AC}" .
		    "\u{AD}\u{3}\u{2}\u{2}\u{2}\u{AD}\u{AE}\u{3}\u{2}\u{2}\u{2}\u{AE}\u{AF}" .
		    "\u{7}\u{2}\u{2}\u{3}\u{AF}\u{3}\u{3}\u{2}\u{2}\u{2}\u{B0}\u{B4}\u{5}" .
		    "\u{6}\u{4}\u{2}\u{B1}\u{B4}\u{5}\u{8}\u{5}\u{2}\u{B2}\u{B4}\u{5}\u{A}" .
		    "\u{6}\u{2}\u{B3}\u{B0}\u{3}\u{2}\u{2}\u{2}\u{B3}\u{B1}\u{3}\u{2}\u{2}" .
		    "\u{2}\u{B3}\u{B2}\u{3}\u{2}\u{2}\u{2}\u{B4}\u{5}\u{3}\u{2}\u{2}\u{2}" .
		    "\u{B5}\u{B6}\u{7}\u{19}\u{2}\u{2}\u{B6}\u{B7}\u{7}\u{39}\u{2}\u{2}" .
		    "\u{B7}\u{7}\u{3}\u{2}\u{2}\u{2}\u{B8}\u{B9}\u{7}\u{1E}\u{2}\u{2}\u{B9}" .
		    "\u{BA}\u{7}\u{3A}\u{2}\u{2}\u{BA}\u{BB}\u{7}\u{39}\u{2}\u{2}\u{BB}" .
		    "\u{9}\u{3}\u{2}\u{2}\u{2}\u{BC}\u{BD}\u{7}\u{1B}\u{2}\u{2}\u{BD}\u{BE}" .
		    "\u{7}\u{39}\u{2}\u{2}\u{BE}\u{B}\u{3}\u{2}\u{2}\u{2}\u{BF}\u{C2}\u{5}" .
		    "\u{E}\u{8}\u{2}\u{C0}\u{C2}\u{5}\u{14}\u{B}\u{2}\u{C1}\u{BF}\u{3}" .
		    "\u{2}\u{2}\u{2}\u{C1}\u{C0}\u{3}\u{2}\u{2}\u{2}\u{C2}\u{D}\u{3}\u{2}" .
		    "\u{2}\u{2}\u{C3}\u{C4}\u{7}\u{1F}\u{2}\u{2}\u{C4}\u{C5}\u{7}\u{3}" .
		    "\u{2}\u{2}\u{C5}\u{C6}\u{5}\u{16}\u{C}\u{2}\u{C6}\u{F}\u{3}\u{2}\u{2}" .
		    "\u{2}\u{C7}\u{C9}\u{5}\u{7C}\u{3F}\u{2}\u{C8}\u{C7}\u{3}\u{2}\u{2}" .
		    "\u{2}\u{C9}\u{CA}\u{3}\u{2}\u{2}\u{2}\u{CA}\u{C8}\u{3}\u{2}\u{2}\u{2}" .
		    "\u{CA}\u{CB}\u{3}\u{2}\u{2}\u{2}\u{CB}\u{11}\u{3}\u{2}\u{2}\u{2}\u{CC}" .
		    "\u{CF}\u{5}\u{4}\u{3}\u{2}\u{CD}\u{CF}\u{5}\u{C}\u{7}\u{2}\u{CE}\u{CC}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{CE}\u{CD}\u{3}\u{2}\u{2}\u{2}\u{CF}\u{13}\u{3}" .
		    "\u{2}\u{2}\u{2}\u{D0}\u{D2}\u{7}\u{18}\u{2}\u{2}\u{D1}\u{D0}\u{3}" .
		    "\u{2}\u{2}\u{2}\u{D1}\u{D2}\u{3}\u{2}\u{2}\u{2}\u{D2}\u{D3}\u{3}\u{2}" .
		    "\u{2}\u{2}\u{D3}\u{D7}\u{5}\u{86}\u{44}\u{2}\u{D4}\u{D6}\u{5}\u{9A}" .
		    "\u{4E}\u{2}\u{D5}\u{D4}\u{3}\u{2}\u{2}\u{2}\u{D6}\u{D9}\u{3}\u{2}" .
		    "\u{2}\u{2}\u{D7}\u{D5}\u{3}\u{2}\u{2}\u{2}\u{D7}\u{D8}\u{3}\u{2}\u{2}" .
		    "\u{2}\u{D8}\u{DC}\u{3}\u{2}\u{2}\u{2}\u{D9}\u{D7}\u{3}\u{2}\u{2}\u{2}" .
		    "\u{DA}\u{DD}\u{5}\u{16}\u{C}\u{2}\u{DB}\u{DD}\u{7}\u{1D}\u{2}\u{2}" .
		    "\u{DC}\u{DA}\u{3}\u{2}\u{2}\u{2}\u{DC}\u{DB}\u{3}\u{2}\u{2}\u{2}\u{DD}" .
		    "\u{15}\u{3}\u{2}\u{2}\u{2}\u{DE}\u{DF}\u{5}\u{1A}\u{E}\u{2}\u{DF}" .
		    "\u{17}\u{3}\u{2}\u{2}\u{2}\u{E0}\u{E1}\u{5}\u{1C}\u{F}\u{2}\u{E1}" .
		    "\u{19}\u{3}\u{2}\u{2}\u{2}\u{E2}\u{E7}\u{5}\u{1E}\u{10}\u{2}\u{E3}" .
		    "\u{E4}\u{7}\u{28}\u{2}\u{2}\u{E4}\u{E6}\u{5}\u{1E}\u{10}\u{2}\u{E5}" .
		    "\u{E3}\u{3}\u{2}\u{2}\u{2}\u{E6}\u{E9}\u{3}\u{2}\u{2}\u{2}\u{E7}\u{E5}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{E7}\u{E8}\u{3}\u{2}\u{2}\u{2}\u{E8}\u{1B}\u{3}" .
		    "\u{2}\u{2}\u{2}\u{E9}\u{E7}\u{3}\u{2}\u{2}\u{2}\u{EA}\u{EF}\u{5}\u{20}" .
		    "\u{11}\u{2}\u{EB}\u{EC}\u{7}\u{28}\u{2}\u{2}\u{EC}\u{EE}\u{5}\u{20}" .
		    "\u{11}\u{2}\u{ED}\u{EB}\u{3}\u{2}\u{2}\u{2}\u{EE}\u{F1}\u{3}\u{2}" .
		    "\u{2}\u{2}\u{EF}\u{ED}\u{3}\u{2}\u{2}\u{2}\u{EF}\u{F0}\u{3}\u{2}\u{2}" .
		    "\u{2}\u{F0}\u{1D}\u{3}\u{2}\u{2}\u{2}\u{F1}\u{EF}\u{3}\u{2}\u{2}\u{2}" .
		    "\u{F2}\u{F7}\u{5}\u{22}\u{12}\u{2}\u{F3}\u{F4}\u{7}\u{27}\u{2}\u{2}" .
		    "\u{F4}\u{F6}\u{5}\u{22}\u{12}\u{2}\u{F5}\u{F3}\u{3}\u{2}\u{2}\u{2}" .
		    "\u{F6}\u{F9}\u{3}\u{2}\u{2}\u{2}\u{F7}\u{F5}\u{3}\u{2}\u{2}\u{2}\u{F7}" .
		    "\u{F8}\u{3}\u{2}\u{2}\u{2}\u{F8}\u{1F}\u{3}\u{2}\u{2}\u{2}\u{F9}\u{F7}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{FA}\u{FF}\u{5}\u{24}\u{13}\u{2}\u{FB}\u{FC}" .
		    "\u{7}\u{27}\u{2}\u{2}\u{FC}\u{FE}\u{5}\u{24}\u{13}\u{2}\u{FD}\u{FB}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{FE}\u{101}\u{3}\u{2}\u{2}\u{2}\u{FF}\u{FD}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{FF}\u{100}\u{3}\u{2}\u{2}\u{2}\u{100}\u{21}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{101}\u{FF}\u{3}\u{2}\u{2}\u{2}\u{102}\u{104}" .
		    "\u{7}\u{32}\u{2}\u{2}\u{103}\u{102}\u{3}\u{2}\u{2}\u{2}\u{103}\u{104}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{104}\u{105}\u{3}\u{2}\u{2}\u{2}\u{105}\u{106}" .
		    "\u{5}\u{26}\u{14}\u{2}\u{106}\u{23}\u{3}\u{2}\u{2}\u{2}\u{107}\u{109}" .
		    "\u{7}\u{32}\u{2}\u{2}\u{108}\u{107}\u{3}\u{2}\u{2}\u{2}\u{108}\u{109}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{109}\u{10A}\u{3}\u{2}\u{2}\u{2}\u{10A}\u{10B}" .
		    "\u{5}\u{28}\u{15}\u{2}\u{10B}\u{25}\u{3}\u{2}\u{2}\u{2}\u{10C}\u{10E}" .
		    "\u{5}\u{36}\u{1C}\u{2}\u{10D}\u{10F}\u{5}\u{2A}\u{16}\u{2}\u{10E}" .
		    "\u{10D}\u{3}\u{2}\u{2}\u{2}\u{10E}\u{10F}\u{3}\u{2}\u{2}\u{2}\u{10F}" .
		    "\u{11B}\u{3}\u{2}\u{2}\u{2}\u{110}\u{11B}\u{5}\u{32}\u{1A}\u{2}\u{111}" .
		    "\u{113}\u{5}\u{2A}\u{16}\u{2}\u{112}\u{114}\u{5}\u{36}\u{1C}\u{2}" .
		    "\u{113}\u{112}\u{3}\u{2}\u{2}\u{2}\u{113}\u{114}\u{3}\u{2}\u{2}\u{2}" .
		    "\u{114}\u{11B}\u{3}\u{2}\u{2}\u{2}\u{115}\u{116}\u{7}\u{4}\u{2}\u{2}" .
		    "\u{116}\u{117}\u{5}\u{16}\u{C}\u{2}\u{117}\u{118}\u{7}\u{5}\u{2}\u{2}" .
		    "\u{118}\u{11B}\u{3}\u{2}\u{2}\u{2}\u{119}\u{11B}\u{7}\u{6}\u{2}\u{2}" .
		    "\u{11A}\u{10C}\u{3}\u{2}\u{2}\u{2}\u{11A}\u{110}\u{3}\u{2}\u{2}\u{2}" .
		    "\u{11A}\u{111}\u{3}\u{2}\u{2}\u{2}\u{11A}\u{115}\u{3}\u{2}\u{2}\u{2}" .
		    "\u{11A}\u{119}\u{3}\u{2}\u{2}\u{2}\u{11B}\u{27}\u{3}\u{2}\u{2}\u{2}" .
		    "\u{11C}\u{11E}\u{5}\u{34}\u{1B}\u{2}\u{11D}\u{11F}\u{5}\u{2C}\u{17}" .
		    "\u{2}\u{11E}\u{11D}\u{3}\u{2}\u{2}\u{2}\u{11E}\u{11F}\u{3}\u{2}\u{2}" .
		    "\u{2}\u{11F}\u{12B}\u{3}\u{2}\u{2}\u{2}\u{120}\u{12B}\u{5}\u{30}\u{19}" .
		    "\u{2}\u{121}\u{123}\u{5}\u{2C}\u{17}\u{2}\u{122}\u{124}\u{5}\u{34}" .
		    "\u{1B}\u{2}\u{123}\u{122}\u{3}\u{2}\u{2}\u{2}\u{123}\u{124}\u{3}\u{2}" .
		    "\u{2}\u{2}\u{124}\u{12B}\u{3}\u{2}\u{2}\u{2}\u{125}\u{126}\u{7}\u{4}" .
		    "\u{2}\u{2}\u{126}\u{127}\u{5}\u{16}\u{C}\u{2}\u{127}\u{128}\u{7}\u{5}" .
		    "\u{2}\u{2}\u{128}\u{12B}\u{3}\u{2}\u{2}\u{2}\u{129}\u{12B}\u{7}\u{6}" .
		    "\u{2}\u{2}\u{12A}\u{11C}\u{3}\u{2}\u{2}\u{2}\u{12A}\u{120}\u{3}\u{2}" .
		    "\u{2}\u{2}\u{12A}\u{121}\u{3}\u{2}\u{2}\u{2}\u{12A}\u{125}\u{3}\u{2}" .
		    "\u{2}\u{2}\u{12A}\u{129}\u{3}\u{2}\u{2}\u{2}\u{12B}\u{29}\u{3}\u{2}" .
		    "\u{2}\u{2}\u{12C}\u{12F}\u{5}\u{48}\u{25}\u{2}\u{12D}\u{12F}\u{5}" .
		    "\u{2E}\u{18}\u{2}\u{12E}\u{12C}\u{3}\u{2}\u{2}\u{2}\u{12E}\u{12D}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{12F}\u{2B}\u{3}\u{2}\u{2}\u{2}\u{130}\u{133}" .
		    "\u{5}\u{4A}\u{26}\u{2}\u{131}\u{133}\u{5}\u{2E}\u{18}\u{2}\u{132}" .
		    "\u{130}\u{3}\u{2}\u{2}\u{2}\u{132}\u{131}\u{3}\u{2}\u{2}\u{2}\u{133}" .
		    "\u{2D}\u{3}\u{2}\u{2}\u{2}\u{134}\u{139}\u{7}\u{3D}\u{2}\u{2}\u{135}" .
		    "\u{139}\u{7}\u{3C}\u{2}\u{2}\u{136}\u{137}\u{7}\u{7}\u{2}\u{2}\u{137}" .
		    "\u{139}\u{5}\u{86}\u{44}\u{2}\u{138}\u{134}\u{3}\u{2}\u{2}\u{2}\u{138}" .
		    "\u{135}\u{3}\u{2}\u{2}\u{2}\u{138}\u{136}\u{3}\u{2}\u{2}\u{2}\u{139}" .
		    "\u{2F}\u{3}\u{2}\u{2}\u{2}\u{13A}\u{13E}\u{7}\u{23}\u{2}\u{2}\u{13B}" .
		    "\u{13D}\u{5}\u{3A}\u{1E}\u{2}\u{13C}\u{13B}\u{3}\u{2}\u{2}\u{2}\u{13D}" .
		    "\u{140}\u{3}\u{2}\u{2}\u{2}\u{13E}\u{13C}\u{3}\u{2}\u{2}\u{2}\u{13E}" .
		    "\u{13F}\u{3}\u{2}\u{2}\u{2}\u{13F}\u{15C}\u{3}\u{2}\u{2}\u{2}\u{140}" .
		    "\u{13E}\u{3}\u{2}\u{2}\u{2}\u{141}\u{145}\u{5}\u{38}\u{1D}\u{2}\u{142}" .
		    "\u{144}\u{5}\u{3C}\u{1F}\u{2}\u{143}\u{142}\u{3}\u{2}\u{2}\u{2}\u{144}" .
		    "\u{147}\u{3}\u{2}\u{2}\u{2}\u{145}\u{143}\u{3}\u{2}\u{2}\u{2}\u{145}" .
		    "\u{146}\u{3}\u{2}\u{2}\u{2}\u{146}\u{15C}\u{3}\u{2}\u{2}\u{2}\u{147}" .
		    "\u{145}\u{3}\u{2}\u{2}\u{2}\u{148}\u{14C}\u{5}\u{84}\u{43}\u{2}\u{149}" .
		    "\u{14B}\u{5}\u{3A}\u{1E}\u{2}\u{14A}\u{149}\u{3}\u{2}\u{2}\u{2}\u{14B}" .
		    "\u{14E}\u{3}\u{2}\u{2}\u{2}\u{14C}\u{14A}\u{3}\u{2}\u{2}\u{2}\u{14C}" .
		    "\u{14D}\u{3}\u{2}\u{2}\u{2}\u{14D}\u{15C}\u{3}\u{2}\u{2}\u{2}\u{14E}" .
		    "\u{14C}\u{3}\u{2}\u{2}\u{2}\u{14F}\u{153}\u{5}\u{68}\u{35}\u{2}\u{150}" .
		    "\u{152}\u{5}\u{3A}\u{1E}\u{2}\u{151}\u{150}\u{3}\u{2}\u{2}\u{2}\u{152}" .
		    "\u{155}\u{3}\u{2}\u{2}\u{2}\u{153}\u{151}\u{3}\u{2}\u{2}\u{2}\u{153}" .
		    "\u{154}\u{3}\u{2}\u{2}\u{2}\u{154}\u{15C}\u{3}\u{2}\u{2}\u{2}\u{155}" .
		    "\u{153}\u{3}\u{2}\u{2}\u{2}\u{156}\u{158}\u{5}\u{40}\u{21}\u{2}\u{157}" .
		    "\u{156}\u{3}\u{2}\u{2}\u{2}\u{158}\u{159}\u{3}\u{2}\u{2}\u{2}\u{159}" .
		    "\u{157}\u{3}\u{2}\u{2}\u{2}\u{159}\u{15A}\u{3}\u{2}\u{2}\u{2}\u{15A}" .
		    "\u{15C}\u{3}\u{2}\u{2}\u{2}\u{15B}\u{13A}\u{3}\u{2}\u{2}\u{2}\u{15B}" .
		    "\u{141}\u{3}\u{2}\u{2}\u{2}\u{15B}\u{148}\u{3}\u{2}\u{2}\u{2}\u{15B}" .
		    "\u{14F}\u{3}\u{2}\u{2}\u{2}\u{15B}\u{157}\u{3}\u{2}\u{2}\u{2}\u{15C}" .
		    "\u{31}\u{3}\u{2}\u{2}\u{2}\u{15D}\u{161}\u{5}\u{30}\u{19}\u{2}\u{15E}" .
		    "\u{160}\u{5}\u{7A}\u{3E}\u{2}\u{15F}\u{15E}\u{3}\u{2}\u{2}\u{2}\u{160}" .
		    "\u{163}\u{3}\u{2}\u{2}\u{2}\u{161}\u{15F}\u{3}\u{2}\u{2}\u{2}\u{161}" .
		    "\u{162}\u{3}\u{2}\u{2}\u{2}\u{162}\u{167}\u{3}\u{2}\u{2}\u{2}\u{163}" .
		    "\u{161}\u{3}\u{2}\u{2}\u{2}\u{164}\u{166}\u{5}\u{7C}\u{3F}\u{2}\u{165}" .
		    "\u{164}\u{3}\u{2}\u{2}\u{2}\u{166}\u{169}\u{3}\u{2}\u{2}\u{2}\u{167}" .
		    "\u{165}\u{3}\u{2}\u{2}\u{2}\u{167}\u{168}\u{3}\u{2}\u{2}\u{2}\u{168}" .
		    "\u{33}\u{3}\u{2}\u{2}\u{2}\u{169}\u{167}\u{3}\u{2}\u{2}\u{2}\u{16A}" .
		    "\u{16E}\u{5}\u{38}\u{1D}\u{2}\u{16B}\u{16D}\u{5}\u{3C}\u{1F}\u{2}" .
		    "\u{16C}\u{16B}\u{3}\u{2}\u{2}\u{2}\u{16D}\u{170}\u{3}\u{2}\u{2}\u{2}" .
		    "\u{16E}\u{16C}\u{3}\u{2}\u{2}\u{2}\u{16E}\u{16F}\u{3}\u{2}\u{2}\u{2}" .
		    "\u{16F}\u{177}\u{3}\u{2}\u{2}\u{2}\u{170}\u{16E}\u{3}\u{2}\u{2}\u{2}" .
		    "\u{171}\u{173}\u{5}\u{3C}\u{1F}\u{2}\u{172}\u{171}\u{3}\u{2}\u{2}" .
		    "\u{2}\u{173}\u{174}\u{3}\u{2}\u{2}\u{2}\u{174}\u{172}\u{3}\u{2}\u{2}" .
		    "\u{2}\u{174}\u{175}\u{3}\u{2}\u{2}\u{2}\u{175}\u{177}\u{3}\u{2}\u{2}" .
		    "\u{2}\u{176}\u{16A}\u{3}\u{2}\u{2}\u{2}\u{176}\u{172}\u{3}\u{2}\u{2}" .
		    "\u{2}\u{177}\u{35}\u{3}\u{2}\u{2}\u{2}\u{178}\u{17C}\u{5}\u{34}\u{1B}" .
		    "\u{2}\u{179}\u{17B}\u{5}\u{7A}\u{3E}\u{2}\u{17A}\u{179}\u{3}\u{2}" .
		    "\u{2}\u{2}\u{17B}\u{17E}\u{3}\u{2}\u{2}\u{2}\u{17C}\u{17A}\u{3}\u{2}" .
		    "\u{2}\u{2}\u{17C}\u{17D}\u{3}\u{2}\u{2}\u{2}\u{17D}\u{182}\u{3}\u{2}" .
		    "\u{2}\u{2}\u{17E}\u{17C}\u{3}\u{2}\u{2}\u{2}\u{17F}\u{181}\u{5}\u{7C}" .
		    "\u{3F}\u{2}\u{180}\u{17F}\u{3}\u{2}\u{2}\u{2}\u{181}\u{184}\u{3}\u{2}" .
		    "\u{2}\u{2}\u{182}\u{180}\u{3}\u{2}\u{2}\u{2}\u{182}\u{183}\u{3}\u{2}" .
		    "\u{2}\u{2}\u{183}\u{37}\u{3}\u{2}\u{2}\u{2}\u{184}\u{182}\u{3}\u{2}" .
		    "\u{2}\u{2}\u{185}\u{186}\u{9}\u{2}\u{2}\u{2}\u{186}\u{39}\u{3}\u{2}" .
		    "\u{2}\u{2}\u{187}\u{18A}\u{5}\u{3C}\u{1F}\u{2}\u{188}\u{18A}\u{5}" .
		    "\u{40}\u{21}\u{2}\u{189}\u{187}\u{3}\u{2}\u{2}\u{2}\u{189}\u{188}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{18A}\u{3B}\u{3}\u{2}\u{2}\u{2}\u{18B}\u{18C}" .
		    "\u{5}\u{3E}\u{20}\u{2}\u{18C}\u{18D}\u{7}\u{42}\u{2}\u{2}\u{18D}\u{193}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{18E}\u{190}\u{7}\u{3E}\u{2}\u{2}\u{18F}\u{191}" .
		    "\u{7}\u{3F}\u{2}\u{2}\u{190}\u{18F}\u{3}\u{2}\u{2}\u{2}\u{190}\u{191}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{191}\u{193}\u{3}\u{2}\u{2}\u{2}\u{192}\u{18B}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{192}\u{18E}\u{3}\u{2}\u{2}\u{2}\u{193}\u{3D}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{194}\u{195}\u{9}\u{3}\u{2}\u{2}\u{195}\u{3F}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{196}\u{197}\u{5}\u{42}\u{22}\u{2}\u{197}\u{198}" .
		    "\u{5}\u{46}\u{24}\u{2}\u{198}\u{19D}\u{3}\u{2}\u{2}\u{2}\u{199}\u{19A}" .
		    "\u{5}\u{44}\u{23}\u{2}\u{19A}\u{19B}\u{7}\u{42}\u{2}\u{2}\u{19B}\u{19D}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{19C}\u{196}\u{3}\u{2}\u{2}\u{2}\u{19C}\u{199}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{19D}\u{41}\u{3}\u{2}\u{2}\u{2}\u{19E}\u{19F}" .
		    "\u{9}\u{4}\u{2}\u{2}\u{19F}\u{43}\u{3}\u{2}\u{2}\u{2}\u{1A0}\u{1A1}" .
		    "\u{9}\u{5}\u{2}\u{2}\u{1A1}\u{45}\u{3}\u{2}\u{2}\u{2}\u{1A2}\u{1A3}" .
		    "\u{9}\u{6}\u{2}\u{2}\u{1A3}\u{47}\u{3}\u{2}\u{2}\u{2}\u{1A4}\u{1A8}" .
		    "\u{5}\u{4A}\u{26}\u{2}\u{1A5}\u{1A7}\u{5}\u{7A}\u{3E}\u{2}\u{1A6}" .
		    "\u{1A5}\u{3}\u{2}\u{2}\u{2}\u{1A7}\u{1AA}\u{3}\u{2}\u{2}\u{2}\u{1A8}" .
		    "\u{1A6}\u{3}\u{2}\u{2}\u{2}\u{1A8}\u{1A9}\u{3}\u{2}\u{2}\u{2}\u{1A9}" .
		    "\u{1AE}\u{3}\u{2}\u{2}\u{2}\u{1AA}\u{1A8}\u{3}\u{2}\u{2}\u{2}\u{1AB}" .
		    "\u{1AD}\u{5}\u{7C}\u{3F}\u{2}\u{1AC}\u{1AB}\u{3}\u{2}\u{2}\u{2}\u{1AD}" .
		    "\u{1B0}\u{3}\u{2}\u{2}\u{2}\u{1AE}\u{1AC}\u{3}\u{2}\u{2}\u{2}\u{1AE}" .
		    "\u{1AF}\u{3}\u{2}\u{2}\u{2}\u{1AF}\u{49}\u{3}\u{2}\u{2}\u{2}\u{1B0}" .
		    "\u{1AE}\u{3}\u{2}\u{2}\u{2}\u{1B1}\u{1B3}\u{5}\u{4C}\u{27}\u{2}\u{1B2}" .
		    "\u{1B1}\u{3}\u{2}\u{2}\u{2}\u{1B3}\u{1B6}\u{3}\u{2}\u{2}\u{2}\u{1B4}" .
		    "\u{1B2}\u{3}\u{2}\u{2}\u{2}\u{1B4}\u{1B5}\u{3}\u{2}\u{2}\u{2}\u{1B5}" .
		    "\u{1B7}\u{3}\u{2}\u{2}\u{2}\u{1B6}\u{1B4}\u{3}\u{2}\u{2}\u{2}\u{1B7}" .
		    "\u{1B9}\u{7}\u{8}\u{2}\u{2}\u{1B8}\u{1BA}\u{5}\u{50}\u{29}\u{2}\u{1B9}" .
		    "\u{1B8}\u{3}\u{2}\u{2}\u{2}\u{1B9}\u{1BA}\u{3}\u{2}\u{2}\u{2}\u{1BA}" .
		    "\u{1BB}\u{3}\u{2}\u{2}\u{2}\u{1BB}\u{1BC}\u{7}\u{9}\u{2}\u{2}\u{1BC}" .
		    "\u{4B}\u{3}\u{2}\u{2}\u{2}\u{1BD}\u{1C1}\u{5}\u{98}\u{4D}\u{2}\u{1BE}" .
		    "\u{1C1}\u{5}\u{4E}\u{28}\u{2}\u{1BF}\u{1C1}\u{7}\u{21}\u{2}\u{2}\u{1C0}" .
		    "\u{1BD}\u{3}\u{2}\u{2}\u{2}\u{1C0}\u{1BE}\u{3}\u{2}\u{2}\u{2}\u{1C0}" .
		    "\u{1BF}\u{3}\u{2}\u{2}\u{2}\u{1C1}\u{4D}\u{3}\u{2}\u{2}\u{2}\u{1C2}" .
		    "\u{1C4}\u{7}\u{22}\u{2}\u{2}\u{1C3}\u{1C5}\u{5}\u{80}\u{41}\u{2}\u{1C4}" .
		    "\u{1C3}\u{3}\u{2}\u{2}\u{2}\u{1C5}\u{1C6}\u{3}\u{2}\u{2}\u{2}\u{1C6}" .
		    "\u{1C4}\u{3}\u{2}\u{2}\u{2}\u{1C6}\u{1C7}\u{3}\u{2}\u{2}\u{2}\u{1C7}" .
		    "\u{4F}\u{3}\u{2}\u{2}\u{2}\u{1C8}\u{1C9}\u{5}\u{52}\u{2A}\u{2}\u{1C9}" .
		    "\u{51}\u{3}\u{2}\u{2}\u{2}\u{1CA}\u{1CD}\u{5}\u{56}\u{2C}\u{2}\u{1CB}" .
		    "\u{1CD}\u{5}\u{54}\u{2B}\u{2}\u{1CC}\u{1CA}\u{3}\u{2}\u{2}\u{2}\u{1CC}" .
		    "\u{1CB}\u{3}\u{2}\u{2}\u{2}\u{1CD}\u{53}\u{3}\u{2}\u{2}\u{2}\u{1CE}" .
		    "\u{1D1}\u{5}\u{56}\u{2C}\u{2}\u{1CF}\u{1D0}\u{7}\u{A}\u{2}\u{2}\u{1D0}" .
		    "\u{1D2}\u{5}\u{56}\u{2C}\u{2}\u{1D1}\u{1CF}\u{3}\u{2}\u{2}\u{2}\u{1D2}" .
		    "\u{1D3}\u{3}\u{2}\u{2}\u{2}\u{1D3}\u{1D1}\u{3}\u{2}\u{2}\u{2}\u{1D3}" .
		    "\u{1D4}\u{3}\u{2}\u{2}\u{2}\u{1D4}\u{55}\u{3}\u{2}\u{2}\u{2}\u{1D5}" .
		    "\u{1D8}\u{5}\u{58}\u{2D}\u{2}\u{1D6}\u{1D8}\u{5}\u{5A}\u{2E}\u{2}" .
		    "\u{1D7}\u{1D5}\u{3}\u{2}\u{2}\u{2}\u{1D7}\u{1D6}\u{3}\u{2}\u{2}\u{2}" .
		    "\u{1D8}\u{57}\u{3}\u{2}\u{2}\u{2}\u{1D9}\u{1DB}\u{5}\u{5C}\u{2F}\u{2}" .
		    "\u{1DA}\u{1DC}\u{7}\u{B}\u{2}\u{2}\u{1DB}\u{1DA}\u{3}\u{2}\u{2}\u{2}" .
		    "\u{1DB}\u{1DC}\u{3}\u{2}\u{2}\u{2}\u{1DC}\u{59}\u{3}\u{2}\u{2}\u{2}" .
		    "\u{1DD}\u{1E0}\u{5}\u{5C}\u{2F}\u{2}\u{1DE}\u{1DF}\u{7}\u{B}\u{2}" .
		    "\u{2}\u{1DF}\u{1E1}\u{5}\u{5C}\u{2F}\u{2}\u{1E0}\u{1DE}\u{3}\u{2}" .
		    "\u{2}\u{2}\u{1E1}\u{1E2}\u{3}\u{2}\u{2}\u{2}\u{1E2}\u{1E0}\u{3}\u{2}" .
		    "\u{2}\u{2}\u{1E2}\u{1E3}\u{3}\u{2}\u{2}\u{2}\u{1E3}\u{1E5}\u{3}\u{2}" .
		    "\u{2}\u{2}\u{1E4}\u{1E6}\u{7}\u{B}\u{2}\u{2}\u{1E5}\u{1E4}\u{3}\u{2}" .
		    "\u{2}\u{2}\u{1E5}\u{1E6}\u{3}\u{2}\u{2}\u{2}\u{1E6}\u{5B}\u{3}\u{2}" .
		    "\u{2}\u{2}\u{1E7}\u{1E8}\u{7}\u{C}\u{2}\u{2}\u{1E8}\u{1EA}\u{5}\u{88}" .
		    "\u{45}\u{2}\u{1E9}\u{1E7}\u{3}\u{2}\u{2}\u{2}\u{1E9}\u{1EA}\u{3}\u{2}" .
		    "\u{2}\u{2}\u{1EA}\u{1ED}\u{3}\u{2}\u{2}\u{2}\u{1EB}\u{1EE}\u{5}\u{60}" .
		    "\u{31}\u{2}\u{1EC}\u{1EE}\u{5}\u{5E}\u{30}\u{2}\u{1ED}\u{1EB}\u{3}" .
		    "\u{2}\u{2}\u{2}\u{1ED}\u{1EC}\u{3}\u{2}\u{2}\u{2}\u{1EE}\u{1F1}\u{3}" .
		    "\u{2}\u{2}\u{2}\u{1EF}\u{1F1}\u{5}\u{78}\u{3D}\u{2}\u{1F0}\u{1E9}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{1F0}\u{1EF}\u{3}\u{2}\u{2}\u{2}\u{1F1}\u{5D}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{1F2}\u{1F3}\u{7}\u{4}\u{2}\u{2}\u{1F3}\u{1F4}" .
		    "\u{5}\u{50}\u{29}\u{2}\u{1F4}\u{1F6}\u{7}\u{5}\u{2}\u{2}\u{1F5}\u{1F7}" .
		    "\u{5}\u{62}\u{32}\u{2}\u{1F6}\u{1F5}\u{3}\u{2}\u{2}\u{2}\u{1F6}\u{1F7}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{1F7}\u{1FB}\u{3}\u{2}\u{2}\u{2}\u{1F8}\u{1FA}" .
		    "\u{5}\u{7A}\u{3E}\u{2}\u{1F9}\u{1F8}\u{3}\u{2}\u{2}\u{2}\u{1FA}\u{1FD}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{1FB}\u{1F9}\u{3}\u{2}\u{2}\u{2}\u{1FB}\u{1FC}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{1FC}\u{201}\u{3}\u{2}\u{2}\u{2}\u{1FD}\u{1FB}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{1FE}\u{200}\u{5}\u{7C}\u{3F}\u{2}\u{1FF}\u{1FE}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{200}\u{203}\u{3}\u{2}\u{2}\u{2}\u{201}\u{1FF}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{201}\u{202}\u{3}\u{2}\u{2}\u{2}\u{202}\u{5F}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{203}\u{201}\u{3}\u{2}\u{2}\u{2}\u{204}\u{206}" .
		    "\u{5}\u{66}\u{34}\u{2}\u{205}\u{204}\u{3}\u{2}\u{2}\u{2}\u{205}\u{206}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{206}\u{207}\u{3}\u{2}\u{2}\u{2}\u{207}\u{208}" .
		    "\u{5}\u{80}\u{41}\u{2}\u{208}\u{20A}\u{5}\u{18}\u{D}\u{2}\u{209}\u{20B}" .
		    "\u{5}\u{62}\u{32}\u{2}\u{20A}\u{209}\u{3}\u{2}\u{2}\u{2}\u{20A}\u{20B}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{20B}\u{20F}\u{3}\u{2}\u{2}\u{2}\u{20C}\u{20E}" .
		    "\u{5}\u{7A}\u{3E}\u{2}\u{20D}\u{20C}\u{3}\u{2}\u{2}\u{2}\u{20E}\u{211}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{20F}\u{20D}\u{3}\u{2}\u{2}\u{2}\u{20F}\u{210}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{210}\u{215}\u{3}\u{2}\u{2}\u{2}\u{211}\u{20F}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{212}\u{214}\u{5}\u{7C}\u{3F}\u{2}\u{213}\u{212}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{214}\u{217}\u{3}\u{2}\u{2}\u{2}\u{215}\u{213}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{215}\u{216}\u{3}\u{2}\u{2}\u{2}\u{216}\u{61}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{217}\u{215}\u{3}\u{2}\u{2}\u{2}\u{218}\u{21D}" .
		    "\u{7}\u{46}\u{2}\u{2}\u{219}\u{21D}\u{7}\u{D}\u{2}\u{2}\u{21A}\u{21D}" .
		    "\u{7}\u{E}\u{2}\u{2}\u{21B}\u{21D}\u{5}\u{64}\u{33}\u{2}\u{21C}\u{218}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{21C}\u{219}\u{3}\u{2}\u{2}\u{2}\u{21C}\u{21A}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{21C}\u{21B}\u{3}\u{2}\u{2}\u{2}\u{21D}\u{63}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{21E}\u{21F}\u{7}\u{8}\u{2}\u{2}\u{21F}\u{220}" .
		    "\u{7}\u{42}\u{2}\u{2}\u{220}\u{229}\u{7}\u{9}\u{2}\u{2}\u{221}\u{222}" .
		    "\u{7}\u{8}\u{2}\u{2}\u{222}\u{223}\u{7}\u{42}\u{2}\u{2}\u{223}\u{225}" .
		    "\u{7}\u{F}\u{2}\u{2}\u{224}\u{226}\u{9}\u{7}\u{2}\u{2}\u{225}\u{224}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{225}\u{226}\u{3}\u{2}\u{2}\u{2}\u{226}\u{227}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{227}\u{229}\u{7}\u{9}\u{2}\u{2}\u{228}\u{21E}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{228}\u{221}\u{3}\u{2}\u{2}\u{2}\u{229}\u{65}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{22A}\u{22B}\u{7}\u{10}\u{2}\u{2}\u{22B}\u{67}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{22C}\u{230}\u{7}\u{11}\u{2}\u{2}\u{22D}\u{22F}" .
		    "\u{5}\u{6A}\u{36}\u{2}\u{22E}\u{22D}\u{3}\u{2}\u{2}\u{2}\u{22F}\u{232}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{230}\u{22E}\u{3}\u{2}\u{2}\u{2}\u{230}\u{231}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{231}\u{233}\u{3}\u{2}\u{2}\u{2}\u{232}\u{230}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{233}\u{234}\u{7}\u{12}\u{2}\u{2}\u{234}\u{69}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{235}\u{24B}\u{5}\u{6C}\u{37}\u{2}\u{236}\u{24B}" .
		    "\u{5}\u{70}\u{39}\u{2}\u{237}\u{24B}\u{5}\u{74}\u{3B}\u{2}\u{238}" .
		    "\u{248}\u{7}\u{6}\u{2}\u{2}\u{239}\u{23B}\u{5}\u{6E}\u{38}\u{2}\u{23A}" .
		    "\u{239}\u{3}\u{2}\u{2}\u{2}\u{23B}\u{23C}\u{3}\u{2}\u{2}\u{2}\u{23C}" .
		    "\u{23A}\u{3}\u{2}\u{2}\u{2}\u{23C}\u{23D}\u{3}\u{2}\u{2}\u{2}\u{23D}" .
		    "\u{249}\u{3}\u{2}\u{2}\u{2}\u{23E}\u{240}\u{5}\u{72}\u{3A}\u{2}\u{23F}" .
		    "\u{23E}\u{3}\u{2}\u{2}\u{2}\u{240}\u{241}\u{3}\u{2}\u{2}\u{2}\u{241}" .
		    "\u{23F}\u{3}\u{2}\u{2}\u{2}\u{241}\u{242}\u{3}\u{2}\u{2}\u{2}\u{242}" .
		    "\u{249}\u{3}\u{2}\u{2}\u{2}\u{243}\u{245}\u{5}\u{76}\u{3C}\u{2}\u{244}" .
		    "\u{243}\u{3}\u{2}\u{2}\u{2}\u{245}\u{246}\u{3}\u{2}\u{2}\u{2}\u{246}" .
		    "\u{244}\u{3}\u{2}\u{2}\u{2}\u{246}\u{247}\u{3}\u{2}\u{2}\u{2}\u{247}" .
		    "\u{249}\u{3}\u{2}\u{2}\u{2}\u{248}\u{23A}\u{3}\u{2}\u{2}\u{2}\u{248}" .
		    "\u{23F}\u{3}\u{2}\u{2}\u{2}\u{248}\u{244}\u{3}\u{2}\u{2}\u{2}\u{249}" .
		    "\u{24B}\u{3}\u{2}\u{2}\u{2}\u{24A}\u{235}\u{3}\u{2}\u{2}\u{2}\u{24A}" .
		    "\u{236}\u{3}\u{2}\u{2}\u{2}\u{24A}\u{237}\u{3}\u{2}\u{2}\u{2}\u{24A}" .
		    "\u{238}\u{3}\u{2}\u{2}\u{2}\u{24B}\u{6B}\u{3}\u{2}\u{2}\u{2}\u{24C}" .
		    "\u{254}\u{5}\u{92}\u{4A}\u{2}\u{24D}\u{251}\u{7}\u{45}\u{2}\u{2}\u{24E}" .
		    "\u{250}\u{5}\u{6E}\u{38}\u{2}\u{24F}\u{24E}\u{3}\u{2}\u{2}\u{2}\u{250}" .
		    "\u{253}\u{3}\u{2}\u{2}\u{2}\u{251}\u{24F}\u{3}\u{2}\u{2}\u{2}\u{251}" .
		    "\u{252}\u{3}\u{2}\u{2}\u{2}\u{252}\u{255}\u{3}\u{2}\u{2}\u{2}\u{253}" .
		    "\u{251}\u{3}\u{2}\u{2}\u{2}\u{254}\u{24D}\u{3}\u{2}\u{2}\u{2}\u{254}" .
		    "\u{255}\u{3}\u{2}\u{2}\u{2}\u{255}\u{6D}\u{3}\u{2}\u{2}\u{2}\u{256}" .
		    "\u{257}\u{7}\u{13}\u{2}\u{2}\u{257}\u{259}\u{5}\u{92}\u{4A}\u{2}\u{258}" .
		    "\u{25A}\u{7}\u{45}\u{2}\u{2}\u{259}\u{258}\u{3}\u{2}\u{2}\u{2}\u{259}" .
		    "\u{25A}\u{3}\u{2}\u{2}\u{2}\u{25A}\u{6F}\u{3}\u{2}\u{2}\u{2}\u{25B}" .
		    "\u{263}\u{5}\u{7E}\u{40}\u{2}\u{25C}\u{260}\u{7}\u{45}\u{2}\u{2}\u{25D}" .
		    "\u{25F}\u{5}\u{72}\u{3A}\u{2}\u{25E}\u{25D}\u{3}\u{2}\u{2}\u{2}\u{25F}" .
		    "\u{262}\u{3}\u{2}\u{2}\u{2}\u{260}\u{25E}\u{3}\u{2}\u{2}\u{2}\u{260}" .
		    "\u{261}\u{3}\u{2}\u{2}\u{2}\u{261}\u{264}\u{3}\u{2}\u{2}\u{2}\u{262}" .
		    "\u{260}\u{3}\u{2}\u{2}\u{2}\u{263}\u{25C}\u{3}\u{2}\u{2}\u{2}\u{263}" .
		    "\u{264}\u{3}\u{2}\u{2}\u{2}\u{264}\u{71}\u{3}\u{2}\u{2}\u{2}\u{265}" .
		    "\u{266}\u{7}\u{13}\u{2}\u{2}\u{266}\u{268}\u{5}\u{7E}\u{40}\u{2}\u{267}" .
		    "\u{269}\u{7}\u{45}\u{2}\u{2}\u{268}\u{267}\u{3}\u{2}\u{2}\u{2}\u{268}" .
		    "\u{269}\u{3}\u{2}\u{2}\u{2}\u{269}\u{73}\u{3}\u{2}\u{2}\u{2}\u{26A}" .
		    "\u{272}\u{7}\u{41}\u{2}\u{2}\u{26B}\u{26F}\u{7}\u{45}\u{2}\u{2}\u{26C}" .
		    "\u{26E}\u{5}\u{76}\u{3C}\u{2}\u{26D}\u{26C}\u{3}\u{2}\u{2}\u{2}\u{26E}" .
		    "\u{271}\u{3}\u{2}\u{2}\u{2}\u{26F}\u{26D}\u{3}\u{2}\u{2}\u{2}\u{26F}" .
		    "\u{270}\u{3}\u{2}\u{2}\u{2}\u{270}\u{273}\u{3}\u{2}\u{2}\u{2}\u{271}" .
		    "\u{26F}\u{3}\u{2}\u{2}\u{2}\u{272}\u{26B}\u{3}\u{2}\u{2}\u{2}\u{272}" .
		    "\u{273}\u{3}\u{2}\u{2}\u{2}\u{273}\u{27D}\u{3}\u{2}\u{2}\u{2}\u{274}" .
		    "\u{275}\u{7}\u{7}\u{2}\u{2}\u{275}\u{279}\u{7}\u{45}\u{2}\u{2}\u{276}" .
		    "\u{278}\u{5}\u{76}\u{3C}\u{2}\u{277}\u{276}\u{3}\u{2}\u{2}\u{2}\u{278}" .
		    "\u{27B}\u{3}\u{2}\u{2}\u{2}\u{279}\u{277}\u{3}\u{2}\u{2}\u{2}\u{279}" .
		    "\u{27A}\u{3}\u{2}\u{2}\u{2}\u{27A}\u{27D}\u{3}\u{2}\u{2}\u{2}\u{27B}" .
		    "\u{279}\u{3}\u{2}\u{2}\u{2}\u{27C}\u{26A}\u{3}\u{2}\u{2}\u{2}\u{27C}" .
		    "\u{274}\u{3}\u{2}\u{2}\u{2}\u{27D}\u{75}\u{3}\u{2}\u{2}\u{2}\u{27E}" .
		    "\u{27F}\u{7}\u{13}\u{2}\u{2}\u{27F}\u{281}\u{7}\u{41}\u{2}\u{2}\u{280}" .
		    "\u{282}\u{7}\u{45}\u{2}\u{2}\u{281}\u{280}\u{3}\u{2}\u{2}\u{2}\u{281}" .
		    "\u{282}\u{3}\u{2}\u{2}\u{2}\u{282}\u{77}\u{3}\u{2}\u{2}\u{2}\u{283}" .
		    "\u{284}\u{7}\u{14}\u{2}\u{2}\u{284}\u{285}\u{5}\u{88}\u{45}\u{2}\u{285}" .
		    "\u{79}\u{3}\u{2}\u{2}\u{2}\u{286}\u{287}\u{7}\u{15}\u{2}\u{2}\u{287}" .
		    "\u{28A}\u{5}\u{80}\u{41}\u{2}\u{288}\u{28B}\u{5}\u{92}\u{4A}\u{2}" .
		    "\u{289}\u{28B}\u{5}\u{7E}\u{40}\u{2}\u{28A}\u{288}\u{3}\u{2}\u{2}" .
		    "\u{2}\u{28A}\u{289}\u{3}\u{2}\u{2}\u{2}\u{28B}\u{7B}\u{3}\u{2}\u{2}" .
		    "\u{2}\u{28C}\u{28D}\u{7}\u{16}\u{2}\u{2}\u{28D}\u{28E}\u{5}\u{92}" .
		    "\u{4A}\u{2}\u{28E}\u{28F}\u{9}\u{8}\u{2}\u{2}\u{28F}\u{7D}\u{3}\u{2}" .
		    "\u{2}\u{2}\u{290}\u{294}\u{5}\u{8C}\u{47}\u{2}\u{291}\u{294}\u{5}" .
		    "\u{8A}\u{46}\u{2}\u{292}\u{294}\u{5}\u{8E}\u{48}\u{2}\u{293}\u{290}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{293}\u{291}\u{3}\u{2}\u{2}\u{2}\u{293}\u{292}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{294}\u{7F}\u{3}\u{2}\u{2}\u{2}\u{295}\u{298}" .
		    "\u{5}\u{92}\u{4A}\u{2}\u{296}\u{298}\u{5}\u{82}\u{42}\u{2}\u{297}" .
		    "\u{295}\u{3}\u{2}\u{2}\u{2}\u{297}\u{296}\u{3}\u{2}\u{2}\u{2}\u{298}" .
		    "\u{81}\u{3}\u{2}\u{2}\u{2}\u{299}\u{29A}\u{7}\u{38}\u{2}\u{2}\u{29A}" .
		    "\u{83}\u{3}\u{2}\u{2}\u{2}\u{29B}\u{29C}\u{5}\u{92}\u{4A}\u{2}\u{29C}" .
		    "\u{85}\u{3}\u{2}\u{2}\u{2}\u{29D}\u{2A0}\u{5}\u{92}\u{4A}\u{2}\u{29E}" .
		    "\u{2A0}\u{5}\u{96}\u{4C}\u{2}\u{29F}\u{29D}\u{3}\u{2}\u{2}\u{2}\u{29F}" .
		    "\u{29E}\u{3}\u{2}\u{2}\u{2}\u{2A0}\u{87}\u{3}\u{2}\u{2}\u{2}\u{2A1}" .
		    "\u{2A4}\u{5}\u{92}\u{4A}\u{2}\u{2A2}\u{2A4}\u{5}\u{96}\u{4C}\u{2}" .
		    "\u{2A3}\u{2A1}\u{3}\u{2}\u{2}\u{2}\u{2A3}\u{2A2}\u{3}\u{2}\u{2}\u{2}" .
		    "\u{2A4}\u{89}\u{3}\u{2}\u{2}\u{2}\u{2A5}\u{2A6}\u{9}\u{6}\u{2}\u{2}" .
		    "\u{2A6}\u{8B}\u{3}\u{2}\u{2}\u{2}\u{2A7}\u{2AB}\u{5}\u{90}\u{49}\u{2}" .
		    "\u{2A8}\u{2AC}\u{7}\u{41}\u{2}\u{2}\u{2A9}\u{2AA}\u{7}\u{17}\u{2}" .
		    "\u{2}\u{2AA}\u{2AC}\u{5}\u{84}\u{43}\u{2}\u{2AB}\u{2A8}\u{3}\u{2}" .
		    "\u{2}\u{2}\u{2AB}\u{2A9}\u{3}\u{2}\u{2}\u{2}\u{2AB}\u{2AC}\u{3}\u{2}" .
		    "\u{2}\u{2}\u{2AC}\u{8D}\u{3}\u{2}\u{2}\u{2}\u{2AD}\u{2AE}\u{9}\u{9}" .
		    "\u{2}\u{2}\u{2AE}\u{8F}\u{3}\u{2}\u{2}\u{2}\u{2AF}\u{2B0}\u{9}\u{A}" .
		    "\u{2}\u{2}\u{2B0}\u{91}\u{3}\u{2}\u{2}\u{2}\u{2B1}\u{2B4}\u{7}\u{39}" .
		    "\u{2}\u{2}\u{2B2}\u{2B4}\u{5}\u{94}\u{4B}\u{2}\u{2B3}\u{2B1}\u{3}" .
		    "\u{2}\u{2}\u{2}\u{2B3}\u{2B2}\u{3}\u{2}\u{2}\u{2}\u{2B4}\u{93}\u{3}" .
		    "\u{2}\u{2}\u{2}\u{2B5}\u{2B6}\u{9}\u{B}\u{2}\u{2}\u{2B6}\u{95}\u{3}" .
		    "\u{2}\u{2}\u{2}\u{2B7}\u{2B8}\u{7}\u{40}\u{2}\u{2}\u{2B8}\u{97}\u{3}" .
		    "\u{2}\u{2}\u{2}\u{2B9}\u{2BA}\u{7}\u{1A}\u{2}\u{2}\u{2BA}\u{2BE}\u{5}" .
		    "\u{86}\u{44}\u{2}\u{2BB}\u{2BC}\u{7}\u{14}\u{2}\u{2}\u{2BC}\u{2BE}" .
		    "\u{5}\u{86}\u{44}\u{2}\u{2BD}\u{2B9}\u{3}\u{2}\u{2}\u{2}\u{2BD}\u{2BB}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{2BE}\u{99}\u{3}\u{2}\u{2}\u{2}\u{2BF}\u{2C0}" .
		    "\u{7}\u{1C}\u{2}\u{2}\u{2C0}\u{2C4}\u{5}\u{86}\u{44}\u{2}\u{2C1}\u{2C2}" .
		    "\u{7}\u{13}\u{2}\u{2}\u{2C2}\u{2C4}\u{5}\u{86}\u{44}\u{2}\u{2C3}\u{2BF}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{2C3}\u{2C1}\u{3}\u{2}\u{2}\u{2}\u{2C4}\u{9B}" .
		    "\u{3}\u{2}\u{2}\u{2}\u{60}\u{9F}\u{A4}\u{A9}\u{AC}\u{B3}\u{C1}\u{CA}" .
		    "\u{CE}\u{D1}\u{D7}\u{DC}\u{E7}\u{EF}\u{F7}\u{FF}\u{103}\u{108}\u{10E}" .
		    "\u{113}\u{11A}\u{11E}\u{123}\u{12A}\u{12E}\u{132}\u{138}\u{13E}\u{145}" .
		    "\u{14C}\u{153}\u{159}\u{15B}\u{161}\u{167}\u{16E}\u{174}\u{176}\u{17C}" .
		    "\u{182}\u{189}\u{190}\u{192}\u{19C}\u{1A8}\u{1AE}\u{1B4}\u{1B9}\u{1C0}" .
		    "\u{1C6}\u{1CC}\u{1D3}\u{1D7}\u{1DB}\u{1E2}\u{1E5}\u{1E9}\u{1ED}\u{1F0}" .
		    "\u{1F6}\u{1FB}\u{201}\u{205}\u{20A}\u{20F}\u{215}\u{21C}\u{225}\u{228}" .
		    "\u{230}\u{23C}\u{241}\u{246}\u{248}\u{24A}\u{251}\u{254}\u{259}\u{260}" .
		    "\u{263}\u{268}\u{26F}\u{272}\u{279}\u{27C}\u{281}\u{28A}\u{293}\u{297}" .
		    "\u{29F}\u{2A3}\u{2AB}\u{2B3}\u{2BD}\u{2C3}";

		protected static $atn;
		protected static $decisionToDFA;
		protected static $sharedContextCache;

		public function __construct(TokenStream $input)
		{
			parent::__construct($input);

			self::initialize();

			$this->interp = new ParserATNSimulator($this, self::$atn, self::$decisionToDFA, self::$sharedContextCache);
		}

		private static function initialize() : void
		{
			if (self::$atn !== null) {
				return;
			}

			RuntimeMetaData::checkVersion('4.8', RuntimeMetaData::VERSION);

			$atn = (new ATNDeserializer())->deserialize(self::SERIALIZED_ATN);

			$decisionToDFA = [];
			for ($i = 0, $count = $atn->getNumberOfDecisions(); $i < $count; $i++) {
				$decisionToDFA[] = new DFA($atn->getDecisionState($i), $i);
			}

			self::$atn = $atn;
			self::$decisionToDFA = $decisionToDFA;
			self::$sharedContextCache = new PredictionContextCache();
		}

		public function getGrammarFileName() : string
		{
			return "ShExDoc.g4";
		}

		public function getRuleNames() : array
		{
			return self::RULE_NAMES;
		}

		public function getSerializedATN() : string
		{
			return self::SERIALIZED_ATN;
		}

		public function getATN() : ATN
		{
			return self::$atn;
		}

		public function getVocabulary() : Vocabulary
        {
            static $vocabulary;

			return $vocabulary = $vocabulary ?? new VocabularyImpl(self::LITERAL_NAMES, self::SYMBOLIC_NAMES);
        }

		/**
		 * @throws RecognitionException
		 */
		public function shExDoc() : Context\ShExDocContext
		{
		    $localContext = new Context\ShExDocContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 0, self::RULE_shExDoc);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(157);
		        $this->errorHandler->sync($this);

		        $_la = $this->input->LA(1);
		        while (((($_la) & ~0x3f) === 0 && ((1 << $_la) & ((1 << self::KW_BASE) | (1 << self::KW_IMPORT) | (1 << self::KW_PREFIX))) !== 0)) {
		        	$this->setState(154);
		        	$this->directive();
		        	$this->setState(159);
		        	$this->errorHandler->sync($this);
		        	$_la = $this->input->LA(1);
		        }
		        $this->setState(170);
		        $this->errorHandler->sync($this);
		        $_la = $this->input->LA(1);

		        if (((($_la) & ~0x3f) === 0 && ((1 << $_la) & ((1 << self::T__19) | (1 << self::KW_ABSTRACT) | (1 << self::KW_START) | (1 << self::IRIREF) | (1 << self::PNAME_NS) | (1 << self::PNAME_LN) | (1 << self::BLANK_NODE_LABEL))) !== 0)) {
		        	$this->setState(162);
		        	$this->errorHandler->sync($this);

		        	switch ($this->input->LA(1)) {
		        	    case self::KW_ABSTRACT:
		        	    case self::KW_START:
		        	    case self::IRIREF:
		        	    case self::PNAME_NS:
		        	    case self::PNAME_LN:
		        	    case self::BLANK_NODE_LABEL:
		        	    	$this->setState(160);
		        	    	$this->notStartAction();
		        	    	break;

		        	    case self::T__19:
		        	    	$this->setState(161);
		        	    	$this->startActions();
		        	    	break;

		        	default:
		        		throw new NoViableAltException($this);
		        	}
		        	$this->setState(167);
		        	$this->errorHandler->sync($this);

		        	$_la = $this->input->LA(1);
		        	while (((($_la) & ~0x3f) === 0 && ((1 << $_la) & ((1 << self::KW_ABSTRACT) | (1 << self::KW_BASE) | (1 << self::KW_IMPORT) | (1 << self::KW_PREFIX) | (1 << self::KW_START) | (1 << self::IRIREF) | (1 << self::PNAME_NS) | (1 << self::PNAME_LN) | (1 << self::BLANK_NODE_LABEL))) !== 0)) {
		        		$this->setState(164);
		        		$this->statement();
		        		$this->setState(169);
		        		$this->errorHandler->sync($this);
		        		$_la = $this->input->LA(1);
		        	}
		        }
		        $this->setState(172);
		        $this->match(self::EOF);
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function directive() : Context\DirectiveContext
		{
		    $localContext = new Context\DirectiveContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 2, self::RULE_directive);

		    try {
		        $this->setState(177);
		        $this->errorHandler->sync($this);

		        switch ($this->input->LA(1)) {
		            case self::KW_BASE:
		            	$this->enterOuterAlt($localContext, 1);
		            	$this->setState(174);
		            	$this->baseDecl();
		            	break;

		            case self::KW_PREFIX:
		            	$this->enterOuterAlt($localContext, 2);
		            	$this->setState(175);
		            	$this->prefixDecl();
		            	break;

		            case self::KW_IMPORT:
		            	$this->enterOuterAlt($localContext, 3);
		            	$this->setState(176);
		            	$this->importDecl();
		            	break;

		        default:
		        	throw new NoViableAltException($this);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function baseDecl() : Context\BaseDeclContext
		{
		    $localContext = new Context\BaseDeclContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 4, self::RULE_baseDecl);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(179);
		        $this->match(self::KW_BASE);
		        $this->setState(180);
		        $this->match(self::IRIREF);
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function prefixDecl() : Context\PrefixDeclContext
		{
		    $localContext = new Context\PrefixDeclContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 6, self::RULE_prefixDecl);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(182);
		        $this->match(self::KW_PREFIX);
		        $this->setState(183);
		        $this->match(self::PNAME_NS);
		        $this->setState(184);
		        $this->match(self::IRIREF);
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function importDecl() : Context\ImportDeclContext
		{
		    $localContext = new Context\ImportDeclContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 8, self::RULE_importDecl);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(186);
		        $this->match(self::KW_IMPORT);
		        $this->setState(187);
		        $this->match(self::IRIREF);
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function notStartAction() : Context\NotStartActionContext
		{
		    $localContext = new Context\NotStartActionContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 10, self::RULE_notStartAction);

		    try {
		        $this->setState(191);
		        $this->errorHandler->sync($this);

		        switch ($this->input->LA(1)) {
		            case self::KW_START:
		            	$this->enterOuterAlt($localContext, 1);
		            	$this->setState(189);
		            	$this->start();
		            	break;

		            case self::KW_ABSTRACT:
		            case self::IRIREF:
		            case self::PNAME_NS:
		            case self::PNAME_LN:
		            case self::BLANK_NODE_LABEL:
		            	$this->enterOuterAlt($localContext, 2);
		            	$this->setState(190);
		            	$this->shapeExprDecl();
		            	break;

		        default:
		        	throw new NoViableAltException($this);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function start() : Context\StartContext
		{
		    $localContext = new Context\StartContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 12, self::RULE_start);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(193);
		        $this->match(self::KW_START);
		        $this->setState(194);
		        $this->match(self::T__0);
		        $this->setState(195);
		        $this->shapeExpression();
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function startActions() : Context\StartActionsContext
		{
		    $localContext = new Context\StartActionsContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 14, self::RULE_startActions);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(198); 
		        $this->errorHandler->sync($this);

		        $_la = $this->input->LA(1);
		        do {
		        	$this->setState(197);
		        	$this->semanticAction();
		        	$this->setState(200); 
		        	$this->errorHandler->sync($this);
		        	$_la = $this->input->LA(1);
		        } while ($_la === self::T__19);
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function statement() : Context\StatementContext
		{
		    $localContext = new Context\StatementContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 16, self::RULE_statement);

		    try {
		        $this->setState(204);
		        $this->errorHandler->sync($this);

		        switch ($this->input->LA(1)) {
		            case self::KW_BASE:
		            case self::KW_IMPORT:
		            case self::KW_PREFIX:
		            	$this->enterOuterAlt($localContext, 1);
		            	$this->setState(202);
		            	$this->directive();
		            	break;

		            case self::KW_ABSTRACT:
		            case self::KW_START:
		            case self::IRIREF:
		            case self::PNAME_NS:
		            case self::PNAME_LN:
		            case self::BLANK_NODE_LABEL:
		            	$this->enterOuterAlt($localContext, 2);
		            	$this->setState(203);
		            	$this->notStartAction();
		            	break;

		        default:
		        	throw new NoViableAltException($this);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function shapeExprDecl() : Context\ShapeExprDeclContext
		{
		    $localContext = new Context\ShapeExprDeclContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 18, self::RULE_shapeExprDecl);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(207);
		        $this->errorHandler->sync($this);
		        $_la = $this->input->LA(1);

		        if ($_la === self::KW_ABSTRACT) {
		        	$this->setState(206);
		        	$this->match(self::KW_ABSTRACT);
		        }
		        $this->setState(209);
		        $this->shapeExprLabel();
		        $this->setState(213);
		        $this->errorHandler->sync($this);

		        $_la = $this->input->LA(1);
		        while ($_la === self::T__16 || $_la === self::KW_RESTRICTS) {
		        	$this->setState(210);
		        	$this->restrictions();
		        	$this->setState(215);
		        	$this->errorHandler->sync($this);
		        	$_la = $this->input->LA(1);
		        }
		        $this->setState(218);
		        $this->errorHandler->sync($this);

		        switch ($this->input->LA(1)) {
		            case self::T__1:
		            case self::T__3:
		            case self::T__4:
		            case self::T__5:
		            case self::T__14:
		            case self::T__17:
		            case self::KW_EXTENDS:
		            case self::KW_CLOSED:
		            case self::KW_EXTRA:
		            case self::KW_LITERAL:
		            case self::KW_IRI:
		            case self::KW_NONLITERAL:
		            case self::KW_BNODE:
		            case self::KW_MININCLUSIVE:
		            case self::KW_MINEXCLUSIVE:
		            case self::KW_MAXINCLUSIVE:
		            case self::KW_MAXEXCLUSIVE:
		            case self::KW_LENGTH:
		            case self::KW_MINLENGTH:
		            case self::KW_MAXLENGTH:
		            case self::KW_TOTALDIGITS:
		            case self::KW_FRACTIONDIGITS:
		            case self::KW_NOT:
		            case self::IRIREF:
		            case self::PNAME_NS:
		            case self::PNAME_LN:
		            case self::ATPNAME_NS:
		            case self::ATPNAME_LN:
		            case self::REGEXP:
		            	$this->setState(216);
		            	$this->shapeExpression();
		            	break;

		            case self::KW_EXTERNAL:
		            	$this->setState(217);
		            	$this->match(self::KW_EXTERNAL);
		            	break;

		        default:
		        	throw new NoViableAltException($this);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function shapeExpression() : Context\ShapeExpressionContext
		{
		    $localContext = new Context\ShapeExpressionContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 20, self::RULE_shapeExpression);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(220);
		        $this->shapeOr();
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function inlineShapeExpression() : Context\InlineShapeExpressionContext
		{
		    $localContext = new Context\InlineShapeExpressionContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 22, self::RULE_inlineShapeExpression);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(222);
		        $this->inlineShapeOr();
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function shapeOr() : Context\ShapeOrContext
		{
		    $localContext = new Context\ShapeOrContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 24, self::RULE_shapeOr);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(224);
		        $this->shapeAnd();
		        $this->setState(229);
		        $this->errorHandler->sync($this);

		        $_la = $this->input->LA(1);
		        while ($_la === self::KW_OR) {
		        	$this->setState(225);
		        	$this->match(self::KW_OR);
		        	$this->setState(226);
		        	$this->shapeAnd();
		        	$this->setState(231);
		        	$this->errorHandler->sync($this);
		        	$_la = $this->input->LA(1);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function inlineShapeOr() : Context\InlineShapeOrContext
		{
		    $localContext = new Context\InlineShapeOrContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 26, self::RULE_inlineShapeOr);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(232);
		        $this->inlineShapeAnd();
		        $this->setState(237);
		        $this->errorHandler->sync($this);

		        $_la = $this->input->LA(1);
		        while ($_la === self::KW_OR) {
		        	$this->setState(233);
		        	$this->match(self::KW_OR);
		        	$this->setState(234);
		        	$this->inlineShapeAnd();
		        	$this->setState(239);
		        	$this->errorHandler->sync($this);
		        	$_la = $this->input->LA(1);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function shapeAnd() : Context\ShapeAndContext
		{
		    $localContext = new Context\ShapeAndContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 28, self::RULE_shapeAnd);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(240);
		        $this->shapeNot();
		        $this->setState(245);
		        $this->errorHandler->sync($this);

		        $_la = $this->input->LA(1);
		        while ($_la === self::KW_AND) {
		        	$this->setState(241);
		        	$this->match(self::KW_AND);
		        	$this->setState(242);
		        	$this->shapeNot();
		        	$this->setState(247);
		        	$this->errorHandler->sync($this);
		        	$_la = $this->input->LA(1);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function inlineShapeAnd() : Context\InlineShapeAndContext
		{
		    $localContext = new Context\InlineShapeAndContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 30, self::RULE_inlineShapeAnd);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(248);
		        $this->inlineShapeNot();
		        $this->setState(253);
		        $this->errorHandler->sync($this);

		        $_la = $this->input->LA(1);
		        while ($_la === self::KW_AND) {
		        	$this->setState(249);
		        	$this->match(self::KW_AND);
		        	$this->setState(250);
		        	$this->inlineShapeNot();
		        	$this->setState(255);
		        	$this->errorHandler->sync($this);
		        	$_la = $this->input->LA(1);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function shapeNot() : Context\ShapeNotContext
		{
		    $localContext = new Context\ShapeNotContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 32, self::RULE_shapeNot);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(257);
		        $this->errorHandler->sync($this);
		        $_la = $this->input->LA(1);

		        if ($_la === self::KW_NOT) {
		        	$this->setState(256);
		        	$this->match(self::KW_NOT);
		        }
		        $this->setState(259);
		        $this->shapeAtom();
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function inlineShapeNot() : Context\InlineShapeNotContext
		{
		    $localContext = new Context\InlineShapeNotContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 34, self::RULE_inlineShapeNot);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(262);
		        $this->errorHandler->sync($this);
		        $_la = $this->input->LA(1);

		        if ($_la === self::KW_NOT) {
		        	$this->setState(261);
		        	$this->match(self::KW_NOT);
		        }
		        $this->setState(264);
		        $this->inlineShapeAtom();
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function shapeAtom() : Context\ShapeAtomContext
		{
		    $localContext = new Context\ShapeAtomContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 36, self::RULE_shapeAtom);

		    try {
		        $this->setState(280);
		        $this->errorHandler->sync($this);

		        switch ($this->getInterpreter()->adaptivePredict($this->input, 19, $this->ctx)) {
		        	case 1:
		        	    $localContext = new Context\ShapeAtomNonLitNodeConstraintContext($localContext);
		        	    $this->enterOuterAlt($localContext, 1);
		        	    $this->setState(266);
		        	    $this->nonLitNodeConstraint();
		        	    $this->setState(268);
		        	    $this->errorHandler->sync($this);
		        	    $_la = $this->input->LA(1);

		        	    if (((($_la) & ~0x3f) === 0 && ((1 << $_la) & ((1 << self::T__4) | (1 << self::T__5) | (1 << self::T__17) | (1 << self::KW_EXTENDS) | (1 << self::KW_CLOSED) | (1 << self::KW_EXTRA) | (1 << self::ATPNAME_NS) | (1 << self::ATPNAME_LN))) !== 0)) {
		        	    	$this->setState(267);
		        	    	$this->shapeOrRef();
		        	    }
		        	break;

		        	case 2:
		        	    $localContext = new Context\ShapeAtomLitNodeConstraintContext($localContext);
		        	    $this->enterOuterAlt($localContext, 2);
		        	    $this->setState(270);
		        	    $this->litNodeConstraint();
		        	break;

		        	case 3:
		        	    $localContext = new Context\ShapeAtomShapeOrRefContext($localContext);
		        	    $this->enterOuterAlt($localContext, 3);
		        	    $this->setState(271);
		        	    $this->shapeOrRef();
		        	    $this->setState(273);
		        	    $this->errorHandler->sync($this);
		        	    $_la = $this->input->LA(1);

		        	    if (((($_la) & ~0x3f) === 0 && ((1 << $_la) & ((1 << self::KW_IRI) | (1 << self::KW_NONLITERAL) | (1 << self::KW_BNODE) | (1 << self::KW_LENGTH) | (1 << self::KW_MINLENGTH) | (1 << self::KW_MAXLENGTH) | (1 << self::REGEXP))) !== 0)) {
		        	    	$this->setState(272);
		        	    	$this->nonLitNodeConstraint();
		        	    }
		        	break;

		        	case 4:
		        	    $localContext = new Context\ShapeAtomShapeExpressionContext($localContext);
		        	    $this->enterOuterAlt($localContext, 4);
		        	    $this->setState(275);
		        	    $this->match(self::T__1);
		        	    $this->setState(276);
		        	    $this->shapeExpression();
		        	    $this->setState(277);
		        	    $this->match(self::T__2);
		        	break;

		        	case 5:
		        	    $localContext = new Context\ShapeAtomAnyContext($localContext);
		        	    $this->enterOuterAlt($localContext, 5);
		        	    $this->setState(279);
		        	    $this->match(self::T__3);
		        	break;
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function inlineShapeAtom() : Context\InlineShapeAtomContext
		{
		    $localContext = new Context\InlineShapeAtomContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 38, self::RULE_inlineShapeAtom);

		    try {
		        $this->setState(296);
		        $this->errorHandler->sync($this);

		        switch ($this->getInterpreter()->adaptivePredict($this->input, 22, $this->ctx)) {
		        	case 1:
		        	    $localContext = new Context\InlineShapeAtomNonLitNodeConstraintContext($localContext);
		        	    $this->enterOuterAlt($localContext, 1);
		        	    $this->setState(282);
		        	    $this->inlineNonLitNodeConstraint();
		        	    $this->setState(284);
		        	    $this->errorHandler->sync($this);

		        	    switch ($this->getInterpreter()->adaptivePredict($this->input, 20, $this->ctx)) {
		        	        case 1:
		        	    	    $this->setState(283);
		        	    	    $this->inlineShapeOrRef();
		        	    	break;
		        	    }
		        	break;

		        	case 2:
		        	    $localContext = new Context\InlineShapeAtomLitNodeConstraintContext($localContext);
		        	    $this->enterOuterAlt($localContext, 2);
		        	    $this->setState(286);
		        	    $this->inlineLitNodeConstraint();
		        	break;

		        	case 3:
		        	    $localContext = new Context\InlineShapeAtomShapeOrRefContext($localContext);
		        	    $this->enterOuterAlt($localContext, 3);
		        	    $this->setState(287);
		        	    $this->inlineShapeOrRef();
		        	    $this->setState(289);
		        	    $this->errorHandler->sync($this);
		        	    $_la = $this->input->LA(1);

		        	    if (((($_la) & ~0x3f) === 0 && ((1 << $_la) & ((1 << self::KW_IRI) | (1 << self::KW_NONLITERAL) | (1 << self::KW_BNODE) | (1 << self::KW_LENGTH) | (1 << self::KW_MINLENGTH) | (1 << self::KW_MAXLENGTH) | (1 << self::REGEXP))) !== 0)) {
		        	    	$this->setState(288);
		        	    	$this->inlineNonLitNodeConstraint();
		        	    }
		        	break;

		        	case 4:
		        	    $localContext = new Context\InlineShapeAtomShapeExpressionContext($localContext);
		        	    $this->enterOuterAlt($localContext, 4);
		        	    $this->setState(291);
		        	    $this->match(self::T__1);
		        	    $this->setState(292);
		        	    $this->shapeExpression();
		        	    $this->setState(293);
		        	    $this->match(self::T__2);
		        	break;

		        	case 5:
		        	    $localContext = new Context\InlineShapeAtomAnyContext($localContext);
		        	    $this->enterOuterAlt($localContext, 5);
		        	    $this->setState(295);
		        	    $this->match(self::T__3);
		        	break;
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function shapeOrRef() : Context\ShapeOrRefContext
		{
		    $localContext = new Context\ShapeOrRefContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 40, self::RULE_shapeOrRef);

		    try {
		        $this->setState(300);
		        $this->errorHandler->sync($this);

		        switch ($this->input->LA(1)) {
		            case self::T__5:
		            case self::T__17:
		            case self::KW_EXTENDS:
		            case self::KW_CLOSED:
		            case self::KW_EXTRA:
		            	$this->enterOuterAlt($localContext, 1);
		            	$this->setState(298);
		            	$this->shapeDefinition();
		            	break;

		            case self::T__4:
		            case self::ATPNAME_NS:
		            case self::ATPNAME_LN:
		            	$this->enterOuterAlt($localContext, 2);
		            	$this->setState(299);
		            	$this->shapeRef();
		            	break;

		        default:
		        	throw new NoViableAltException($this);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function inlineShapeOrRef() : Context\InlineShapeOrRefContext
		{
		    $localContext = new Context\InlineShapeOrRefContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 42, self::RULE_inlineShapeOrRef);

		    try {
		        $this->setState(304);
		        $this->errorHandler->sync($this);

		        switch ($this->input->LA(1)) {
		            case self::T__5:
		            case self::T__17:
		            case self::KW_EXTENDS:
		            case self::KW_CLOSED:
		            case self::KW_EXTRA:
		            	$this->enterOuterAlt($localContext, 1);
		            	$this->setState(302);
		            	$this->inlineShapeDefinition();
		            	break;

		            case self::T__4:
		            case self::ATPNAME_NS:
		            case self::ATPNAME_LN:
		            	$this->enterOuterAlt($localContext, 2);
		            	$this->setState(303);
		            	$this->shapeRef();
		            	break;

		        default:
		        	throw new NoViableAltException($this);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function shapeRef() : Context\ShapeRefContext
		{
		    $localContext = new Context\ShapeRefContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 44, self::RULE_shapeRef);

		    try {
		        $this->setState(310);
		        $this->errorHandler->sync($this);

		        switch ($this->input->LA(1)) {
		            case self::ATPNAME_LN:
		            	$this->enterOuterAlt($localContext, 1);
		            	$this->setState(306);
		            	$this->match(self::ATPNAME_LN);
		            	break;

		            case self::ATPNAME_NS:
		            	$this->enterOuterAlt($localContext, 2);
		            	$this->setState(307);
		            	$this->match(self::ATPNAME_NS);
		            	break;

		            case self::T__4:
		            	$this->enterOuterAlt($localContext, 3);
		            	$this->setState(308);
		            	$this->match(self::T__4);
		            	$this->setState(309);
		            	$this->shapeExprLabel();
		            	break;

		        default:
		        	throw new NoViableAltException($this);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function inlineLitNodeConstraint() : Context\InlineLitNodeConstraintContext
		{
		    $localContext = new Context\InlineLitNodeConstraintContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 46, self::RULE_inlineLitNodeConstraint);

		    try {
		        $this->setState(345);
		        $this->errorHandler->sync($this);

		        switch ($this->input->LA(1)) {
		            case self::KW_LITERAL:
		            	$localContext = new Context\NodeConstraintLiteralContext($localContext);
		            	$this->enterOuterAlt($localContext, 1);
		            	$this->setState(312);
		            	$this->match(self::KW_LITERAL);
		            	$this->setState(316);
		            	$this->errorHandler->sync($this);

		            	$_la = $this->input->LA(1);
		            	while (((($_la) & ~0x3f) === 0 && ((1 << $_la) & ((1 << self::KW_MININCLUSIVE) | (1 << self::KW_MINEXCLUSIVE) | (1 << self::KW_MAXINCLUSIVE) | (1 << self::KW_MAXEXCLUSIVE) | (1 << self::KW_LENGTH) | (1 << self::KW_MINLENGTH) | (1 << self::KW_MAXLENGTH) | (1 << self::KW_TOTALDIGITS) | (1 << self::KW_FRACTIONDIGITS) | (1 << self::REGEXP))) !== 0)) {
		            		$this->setState(313);
		            		$this->xsFacet();
		            		$this->setState(318);
		            		$this->errorHandler->sync($this);
		            		$_la = $this->input->LA(1);
		            	}
		            	break;

		            case self::KW_IRI:
		            case self::KW_NONLITERAL:
		            case self::KW_BNODE:
		            	$localContext = new Context\NodeConstraintNonLiteralContext($localContext);
		            	$this->enterOuterAlt($localContext, 2);
		            	$this->setState(319);
		            	$this->nonLiteralKind();
		            	$this->setState(323);
		            	$this->errorHandler->sync($this);

		            	$_la = $this->input->LA(1);
		            	while (((($_la) & ~0x3f) === 0 && ((1 << $_la) & ((1 << self::KW_LENGTH) | (1 << self::KW_MINLENGTH) | (1 << self::KW_MAXLENGTH) | (1 << self::REGEXP))) !== 0)) {
		            		$this->setState(320);
		            		$this->stringFacet();
		            		$this->setState(325);
		            		$this->errorHandler->sync($this);
		            		$_la = $this->input->LA(1);
		            	}
		            	break;

		            case self::IRIREF:
		            case self::PNAME_NS:
		            case self::PNAME_LN:
		            	$localContext = new Context\NodeConstraintDatatypeContext($localContext);
		            	$this->enterOuterAlt($localContext, 3);
		            	$this->setState(326);
		            	$this->datatype();
		            	$this->setState(330);
		            	$this->errorHandler->sync($this);

		            	$_la = $this->input->LA(1);
		            	while (((($_la) & ~0x3f) === 0 && ((1 << $_la) & ((1 << self::KW_MININCLUSIVE) | (1 << self::KW_MINEXCLUSIVE) | (1 << self::KW_MAXINCLUSIVE) | (1 << self::KW_MAXEXCLUSIVE) | (1 << self::KW_LENGTH) | (1 << self::KW_MINLENGTH) | (1 << self::KW_MAXLENGTH) | (1 << self::KW_TOTALDIGITS) | (1 << self::KW_FRACTIONDIGITS) | (1 << self::REGEXP))) !== 0)) {
		            		$this->setState(327);
		            		$this->xsFacet();
		            		$this->setState(332);
		            		$this->errorHandler->sync($this);
		            		$_la = $this->input->LA(1);
		            	}
		            	break;

		            case self::T__14:
		            	$localContext = new Context\NodeConstraintValueSetContext($localContext);
		            	$this->enterOuterAlt($localContext, 4);
		            	$this->setState(333);
		            	$this->valueSet();
		            	$this->setState(337);
		            	$this->errorHandler->sync($this);

		            	$_la = $this->input->LA(1);
		            	while (((($_la) & ~0x3f) === 0 && ((1 << $_la) & ((1 << self::KW_MININCLUSIVE) | (1 << self::KW_MINEXCLUSIVE) | (1 << self::KW_MAXINCLUSIVE) | (1 << self::KW_MAXEXCLUSIVE) | (1 << self::KW_LENGTH) | (1 << self::KW_MINLENGTH) | (1 << self::KW_MAXLENGTH) | (1 << self::KW_TOTALDIGITS) | (1 << self::KW_FRACTIONDIGITS) | (1 << self::REGEXP))) !== 0)) {
		            		$this->setState(334);
		            		$this->xsFacet();
		            		$this->setState(339);
		            		$this->errorHandler->sync($this);
		            		$_la = $this->input->LA(1);
		            	}
		            	break;

		            case self::KW_MININCLUSIVE:
		            case self::KW_MINEXCLUSIVE:
		            case self::KW_MAXINCLUSIVE:
		            case self::KW_MAXEXCLUSIVE:
		            case self::KW_TOTALDIGITS:
		            case self::KW_FRACTIONDIGITS:
		            	$localContext = new Context\NodeConstraintNumericFacetContext($localContext);
		            	$this->enterOuterAlt($localContext, 5);
		            	$this->setState(341); 
		            	$this->errorHandler->sync($this);

		            	$_la = $this->input->LA(1);
		            	do {
		            		$this->setState(340);
		            		$this->numericFacet();
		            		$this->setState(343); 
		            		$this->errorHandler->sync($this);
		            		$_la = $this->input->LA(1);
		            	} while (((($_la) & ~0x3f) === 0 && ((1 << $_la) & ((1 << self::KW_MININCLUSIVE) | (1 << self::KW_MINEXCLUSIVE) | (1 << self::KW_MAXINCLUSIVE) | (1 << self::KW_MAXEXCLUSIVE) | (1 << self::KW_TOTALDIGITS) | (1 << self::KW_FRACTIONDIGITS))) !== 0));
		            	break;

		        default:
		        	throw new NoViableAltException($this);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function litNodeConstraint() : Context\LitNodeConstraintContext
		{
		    $localContext = new Context\LitNodeConstraintContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 48, self::RULE_litNodeConstraint);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(347);
		        $this->inlineLitNodeConstraint();
		        $this->setState(351);
		        $this->errorHandler->sync($this);

		        $_la = $this->input->LA(1);
		        while ($_la === self::T__18) {
		        	$this->setState(348);
		        	$this->annotation();
		        	$this->setState(353);
		        	$this->errorHandler->sync($this);
		        	$_la = $this->input->LA(1);
		        }
		        $this->setState(357);
		        $this->errorHandler->sync($this);

		        $_la = $this->input->LA(1);
		        while ($_la === self::T__19) {
		        	$this->setState(354);
		        	$this->semanticAction();
		        	$this->setState(359);
		        	$this->errorHandler->sync($this);
		        	$_la = $this->input->LA(1);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function inlineNonLitNodeConstraint() : Context\InlineNonLitNodeConstraintContext
		{
		    $localContext = new Context\InlineNonLitNodeConstraintContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 50, self::RULE_inlineNonLitNodeConstraint);

		    try {
		        $this->setState(372);
		        $this->errorHandler->sync($this);

		        switch ($this->input->LA(1)) {
		            case self::KW_IRI:
		            case self::KW_NONLITERAL:
		            case self::KW_BNODE:
		            	$localContext = new Context\LitNodeConstraintLiteralContext($localContext);
		            	$this->enterOuterAlt($localContext, 1);
		            	$this->setState(360);
		            	$this->nonLiteralKind();
		            	$this->setState(364);
		            	$this->errorHandler->sync($this);

		            	$_la = $this->input->LA(1);
		            	while (((($_la) & ~0x3f) === 0 && ((1 << $_la) & ((1 << self::KW_LENGTH) | (1 << self::KW_MINLENGTH) | (1 << self::KW_MAXLENGTH) | (1 << self::REGEXP))) !== 0)) {
		            		$this->setState(361);
		            		$this->stringFacet();
		            		$this->setState(366);
		            		$this->errorHandler->sync($this);
		            		$_la = $this->input->LA(1);
		            	}
		            	break;

		            case self::KW_LENGTH:
		            case self::KW_MINLENGTH:
		            case self::KW_MAXLENGTH:
		            case self::REGEXP:
		            	$localContext = new Context\LitNodeConstraintStringFacetContext($localContext);
		            	$this->enterOuterAlt($localContext, 2);
		            	$this->setState(368); 
		            	$this->errorHandler->sync($this);

		            	$_la = $this->input->LA(1);
		            	do {
		            		$this->setState(367);
		            		$this->stringFacet();
		            		$this->setState(370); 
		            		$this->errorHandler->sync($this);
		            		$_la = $this->input->LA(1);
		            	} while (((($_la) & ~0x3f) === 0 && ((1 << $_la) & ((1 << self::KW_LENGTH) | (1 << self::KW_MINLENGTH) | (1 << self::KW_MAXLENGTH) | (1 << self::REGEXP))) !== 0));
		            	break;

		        default:
		        	throw new NoViableAltException($this);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function nonLitNodeConstraint() : Context\NonLitNodeConstraintContext
		{
		    $localContext = new Context\NonLitNodeConstraintContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 52, self::RULE_nonLitNodeConstraint);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(374);
		        $this->inlineNonLitNodeConstraint();
		        $this->setState(378);
		        $this->errorHandler->sync($this);

		        $_la = $this->input->LA(1);
		        while ($_la === self::T__18) {
		        	$this->setState(375);
		        	$this->annotation();
		        	$this->setState(380);
		        	$this->errorHandler->sync($this);
		        	$_la = $this->input->LA(1);
		        }
		        $this->setState(384);
		        $this->errorHandler->sync($this);

		        $_la = $this->input->LA(1);
		        while ($_la === self::T__19) {
		        	$this->setState(381);
		        	$this->semanticAction();
		        	$this->setState(386);
		        	$this->errorHandler->sync($this);
		        	$_la = $this->input->LA(1);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function nonLiteralKind() : Context\NonLiteralKindContext
		{
		    $localContext = new Context\NonLiteralKindContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 54, self::RULE_nonLiteralKind);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(387);

		        $_la = $this->input->LA(1);

		        if (!(((($_la) & ~0x3f) === 0 && ((1 << $_la) & ((1 << self::KW_IRI) | (1 << self::KW_NONLITERAL) | (1 << self::KW_BNODE))) !== 0))) {
		        $this->errorHandler->recoverInline($this);
		        } else {
		        	if ($this->input->LA(1) === Token::EOF) {
		        	    $this->matchedEOF = true;
		            }

		        	$this->errorHandler->reportMatch($this);
		        	$this->consume();
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function xsFacet() : Context\XsFacetContext
		{
		    $localContext = new Context\XsFacetContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 56, self::RULE_xsFacet);

		    try {
		        $this->setState(391);
		        $this->errorHandler->sync($this);

		        switch ($this->input->LA(1)) {
		            case self::KW_LENGTH:
		            case self::KW_MINLENGTH:
		            case self::KW_MAXLENGTH:
		            case self::REGEXP:
		            	$this->enterOuterAlt($localContext, 1);
		            	$this->setState(389);
		            	$this->stringFacet();
		            	break;

		            case self::KW_MININCLUSIVE:
		            case self::KW_MINEXCLUSIVE:
		            case self::KW_MAXINCLUSIVE:
		            case self::KW_MAXEXCLUSIVE:
		            case self::KW_TOTALDIGITS:
		            case self::KW_FRACTIONDIGITS:
		            	$this->enterOuterAlt($localContext, 2);
		            	$this->setState(390);
		            	$this->numericFacet();
		            	break;

		        default:
		        	throw new NoViableAltException($this);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function stringFacet() : Context\StringFacetContext
		{
		    $localContext = new Context\StringFacetContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 58, self::RULE_stringFacet);

		    try {
		        $this->setState(400);
		        $this->errorHandler->sync($this);

		        switch ($this->input->LA(1)) {
		            case self::KW_LENGTH:
		            case self::KW_MINLENGTH:
		            case self::KW_MAXLENGTH:
		            	$this->enterOuterAlt($localContext, 1);
		            	$this->setState(393);
		            	$this->stringLength();
		            	$this->setState(394);
		            	$this->match(self::INTEGER);
		            	break;

		            case self::REGEXP:
		            	$this->enterOuterAlt($localContext, 2);
		            	$this->setState(396);
		            	$this->match(self::REGEXP);
		            	$this->setState(398);
		            	$this->errorHandler->sync($this);
		            	$_la = $this->input->LA(1);

		            	if ($_la === self::REGEXP_FLAGS) {
		            		$this->setState(397);
		            		$this->match(self::REGEXP_FLAGS);
		            	}
		            	break;

		        default:
		        	throw new NoViableAltException($this);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function stringLength() : Context\StringLengthContext
		{
		    $localContext = new Context\StringLengthContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 60, self::RULE_stringLength);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(402);

		        $_la = $this->input->LA(1);

		        if (!(((($_la) & ~0x3f) === 0 && ((1 << $_la) & ((1 << self::KW_LENGTH) | (1 << self::KW_MINLENGTH) | (1 << self::KW_MAXLENGTH))) !== 0))) {
		        $this->errorHandler->recoverInline($this);
		        } else {
		        	if ($this->input->LA(1) === Token::EOF) {
		        	    $this->matchedEOF = true;
		            }

		        	$this->errorHandler->reportMatch($this);
		        	$this->consume();
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function numericFacet() : Context\NumericFacetContext
		{
		    $localContext = new Context\NumericFacetContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 62, self::RULE_numericFacet);

		    try {
		        $this->setState(410);
		        $this->errorHandler->sync($this);

		        switch ($this->input->LA(1)) {
		            case self::KW_MININCLUSIVE:
		            case self::KW_MINEXCLUSIVE:
		            case self::KW_MAXINCLUSIVE:
		            case self::KW_MAXEXCLUSIVE:
		            	$this->enterOuterAlt($localContext, 1);
		            	$this->setState(404);
		            	$this->numericRange();
		            	$this->setState(405);
		            	$this->rawNumeric();
		            	break;

		            case self::KW_TOTALDIGITS:
		            case self::KW_FRACTIONDIGITS:
		            	$this->enterOuterAlt($localContext, 2);
		            	$this->setState(407);
		            	$this->numericLength();
		            	$this->setState(408);
		            	$this->match(self::INTEGER);
		            	break;

		        default:
		        	throw new NoViableAltException($this);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function numericRange() : Context\NumericRangeContext
		{
		    $localContext = new Context\NumericRangeContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 64, self::RULE_numericRange);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(412);

		        $_la = $this->input->LA(1);

		        if (!(((($_la) & ~0x3f) === 0 && ((1 << $_la) & ((1 << self::KW_MININCLUSIVE) | (1 << self::KW_MINEXCLUSIVE) | (1 << self::KW_MAXINCLUSIVE) | (1 << self::KW_MAXEXCLUSIVE))) !== 0))) {
		        $this->errorHandler->recoverInline($this);
		        } else {
		        	if ($this->input->LA(1) === Token::EOF) {
		        	    $this->matchedEOF = true;
		            }

		        	$this->errorHandler->reportMatch($this);
		        	$this->consume();
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function numericLength() : Context\NumericLengthContext
		{
		    $localContext = new Context\NumericLengthContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 66, self::RULE_numericLength);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(414);

		        $_la = $this->input->LA(1);

		        if (!($_la === self::KW_TOTALDIGITS || $_la === self::KW_FRACTIONDIGITS)) {
		        $this->errorHandler->recoverInline($this);
		        } else {
		        	if ($this->input->LA(1) === Token::EOF) {
		        	    $this->matchedEOF = true;
		            }

		        	$this->errorHandler->reportMatch($this);
		        	$this->consume();
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function rawNumeric() : Context\RawNumericContext
		{
		    $localContext = new Context\RawNumericContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 68, self::RULE_rawNumeric);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(416);

		        $_la = $this->input->LA(1);

		        if (!((((($_la - 64)) & ~0x3f) === 0 && ((1 << ($_la - 64)) & ((1 << (self::INTEGER - 64)) | (1 << (self::DECIMAL - 64)) | (1 << (self::DOUBLE - 64)))) !== 0))) {
		        $this->errorHandler->recoverInline($this);
		        } else {
		        	if ($this->input->LA(1) === Token::EOF) {
		        	    $this->matchedEOF = true;
		            }

		        	$this->errorHandler->reportMatch($this);
		        	$this->consume();
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function shapeDefinition() : Context\ShapeDefinitionContext
		{
		    $localContext = new Context\ShapeDefinitionContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 70, self::RULE_shapeDefinition);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(418);
		        $this->inlineShapeDefinition();
		        $this->setState(422);
		        $this->errorHandler->sync($this);

		        $_la = $this->input->LA(1);
		        while ($_la === self::T__18) {
		        	$this->setState(419);
		        	$this->annotation();
		        	$this->setState(424);
		        	$this->errorHandler->sync($this);
		        	$_la = $this->input->LA(1);
		        }
		        $this->setState(428);
		        $this->errorHandler->sync($this);

		        $_la = $this->input->LA(1);
		        while ($_la === self::T__19) {
		        	$this->setState(425);
		        	$this->semanticAction();
		        	$this->setState(430);
		        	$this->errorHandler->sync($this);
		        	$_la = $this->input->LA(1);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function inlineShapeDefinition() : Context\InlineShapeDefinitionContext
		{
		    $localContext = new Context\InlineShapeDefinitionContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 72, self::RULE_inlineShapeDefinition);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(434);
		        $this->errorHandler->sync($this);

		        $_la = $this->input->LA(1);
		        while (((($_la) & ~0x3f) === 0 && ((1 << $_la) & ((1 << self::T__17) | (1 << self::KW_EXTENDS) | (1 << self::KW_CLOSED) | (1 << self::KW_EXTRA))) !== 0)) {
		        	$this->setState(431);
		        	$this->qualifier();
		        	$this->setState(436);
		        	$this->errorHandler->sync($this);
		        	$_la = $this->input->LA(1);
		        }
		        $this->setState(437);
		        $this->match(self::T__5);
		        $this->setState(439);
		        $this->errorHandler->sync($this);
		        $_la = $this->input->LA(1);

		        if (((($_la) & ~0x3f) === 0 && ((1 << $_la) & ((1 << self::T__1) | (1 << self::T__9) | (1 << self::T__13) | (1 << self::T__17) | (1 << self::RDF_TYPE) | (1 << self::IRIREF) | (1 << self::PNAME_NS) | (1 << self::PNAME_LN))) !== 0)) {
		        	$this->setState(438);
		        	$this->tripleExpression();
		        }
		        $this->setState(441);
		        $this->match(self::T__6);
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function qualifier() : Context\QualifierContext
		{
		    $localContext = new Context\QualifierContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 74, self::RULE_qualifier);

		    try {
		        $this->setState(446);
		        $this->errorHandler->sync($this);

		        switch ($this->input->LA(1)) {
		            case self::T__17:
		            case self::KW_EXTENDS:
		            	$this->enterOuterAlt($localContext, 1);
		            	$this->setState(443);
		            	$this->extension();
		            	break;

		            case self::KW_EXTRA:
		            	$this->enterOuterAlt($localContext, 2);
		            	$this->setState(444);
		            	$this->extraPropertySet();
		            	break;

		            case self::KW_CLOSED:
		            	$this->enterOuterAlt($localContext, 3);
		            	$this->setState(445);
		            	$this->match(self::KW_CLOSED);
		            	break;

		        default:
		        	throw new NoViableAltException($this);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function extraPropertySet() : Context\ExtraPropertySetContext
		{
		    $localContext = new Context\ExtraPropertySetContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 76, self::RULE_extraPropertySet);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(448);
		        $this->match(self::KW_EXTRA);
		        $this->setState(450); 
		        $this->errorHandler->sync($this);

		        $_la = $this->input->LA(1);
		        do {
		        	$this->setState(449);
		        	$this->predicate();
		        	$this->setState(452); 
		        	$this->errorHandler->sync($this);
		        	$_la = $this->input->LA(1);
		        } while (((($_la) & ~0x3f) === 0 && ((1 << $_la) & ((1 << self::RDF_TYPE) | (1 << self::IRIREF) | (1 << self::PNAME_NS) | (1 << self::PNAME_LN))) !== 0));
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function tripleExpression() : Context\TripleExpressionContext
		{
		    $localContext = new Context\TripleExpressionContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 78, self::RULE_tripleExpression);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(454);
		        $this->oneOfTripleExpr();
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function oneOfTripleExpr() : Context\OneOfTripleExprContext
		{
		    $localContext = new Context\OneOfTripleExprContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 80, self::RULE_oneOfTripleExpr);

		    try {
		        $this->setState(458);
		        $this->errorHandler->sync($this);

		        switch ($this->getInterpreter()->adaptivePredict($this->input, 49, $this->ctx)) {
		        	case 1:
		        	    $this->enterOuterAlt($localContext, 1);
		        	    $this->setState(456);
		        	    $this->groupTripleExpr();
		        	break;

		        	case 2:
		        	    $this->enterOuterAlt($localContext, 2);
		        	    $this->setState(457);
		        	    $this->multiElementOneOf();
		        	break;
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function multiElementOneOf() : Context\MultiElementOneOfContext
		{
		    $localContext = new Context\MultiElementOneOfContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 82, self::RULE_multiElementOneOf);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(460);
		        $this->groupTripleExpr();
		        $this->setState(463); 
		        $this->errorHandler->sync($this);

		        $_la = $this->input->LA(1);
		        do {
		        	$this->setState(461);
		        	$this->match(self::T__7);
		        	$this->setState(462);
		        	$this->groupTripleExpr();
		        	$this->setState(465); 
		        	$this->errorHandler->sync($this);
		        	$_la = $this->input->LA(1);
		        } while ($_la === self::T__7);
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function groupTripleExpr() : Context\GroupTripleExprContext
		{
		    $localContext = new Context\GroupTripleExprContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 84, self::RULE_groupTripleExpr);

		    try {
		        $this->setState(469);
		        $this->errorHandler->sync($this);

		        switch ($this->getInterpreter()->adaptivePredict($this->input, 51, $this->ctx)) {
		        	case 1:
		        	    $this->enterOuterAlt($localContext, 1);
		        	    $this->setState(467);
		        	    $this->singleElementGroup();
		        	break;

		        	case 2:
		        	    $this->enterOuterAlt($localContext, 2);
		        	    $this->setState(468);
		        	    $this->multiElementGroup();
		        	break;
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function singleElementGroup() : Context\SingleElementGroupContext
		{
		    $localContext = new Context\SingleElementGroupContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 86, self::RULE_singleElementGroup);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(471);
		        $this->unaryTripleExpr();
		        $this->setState(473);
		        $this->errorHandler->sync($this);
		        $_la = $this->input->LA(1);

		        if ($_la === self::T__8) {
		        	$this->setState(472);
		        	$this->match(self::T__8);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function multiElementGroup() : Context\MultiElementGroupContext
		{
		    $localContext = new Context\MultiElementGroupContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 88, self::RULE_multiElementGroup);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(475);
		        $this->unaryTripleExpr();
		        $this->setState(478); 
		        $this->errorHandler->sync($this);

		        $alt = 1;

		        do {
		        	switch ($alt) {
		        	case 1:
		        		$this->setState(476);
		        		$this->match(self::T__8);
		        		$this->setState(477);
		        		$this->unaryTripleExpr();
		        		break;
		        	default:
		        		throw new NoViableAltException($this);
		        	}

		        	$this->setState(480); 
		        	$this->errorHandler->sync($this);

		        	$alt = $this->getInterpreter()->adaptivePredict($this->input, 53, $this->ctx);
		        } while ($alt !== 2 && $alt !== ATN::INVALID_ALT_NUMBER);
		        $this->setState(483);
		        $this->errorHandler->sync($this);
		        $_la = $this->input->LA(1);

		        if ($_la === self::T__8) {
		        	$this->setState(482);
		        	$this->match(self::T__8);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function unaryTripleExpr() : Context\UnaryTripleExprContext
		{
		    $localContext = new Context\UnaryTripleExprContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 90, self::RULE_unaryTripleExpr);

		    try {
		        $this->setState(494);
		        $this->errorHandler->sync($this);

		        switch ($this->input->LA(1)) {
		            case self::T__1:
		            case self::T__9:
		            case self::T__13:
		            case self::RDF_TYPE:
		            case self::IRIREF:
		            case self::PNAME_NS:
		            case self::PNAME_LN:
		            	$this->enterOuterAlt($localContext, 1);
		            	$this->setState(487);
		            	$this->errorHandler->sync($this);
		            	$_la = $this->input->LA(1);

		            	if ($_la === self::T__9) {
		            		$this->setState(485);
		            		$this->match(self::T__9);
		            		$this->setState(486);
		            		$this->tripleExprLabel();
		            	}
		            	$this->setState(491);
		            	$this->errorHandler->sync($this);

		            	switch ($this->input->LA(1)) {
		            	    case self::T__13:
		            	    case self::RDF_TYPE:
		            	    case self::IRIREF:
		            	    case self::PNAME_NS:
		            	    case self::PNAME_LN:
		            	    	$this->setState(489);
		            	    	$this->tripleConstraint();
		            	    	break;

		            	    case self::T__1:
		            	    	$this->setState(490);
		            	    	$this->bracketedTripleExpr();
		            	    	break;

		            	default:
		            		throw new NoViableAltException($this);
		            	}
		            	break;

		            case self::T__17:
		            	$this->enterOuterAlt($localContext, 2);
		            	$this->setState(493);
		            	$this->r_include();
		            	break;

		        default:
		        	throw new NoViableAltException($this);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function bracketedTripleExpr() : Context\BracketedTripleExprContext
		{
		    $localContext = new Context\BracketedTripleExprContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 92, self::RULE_bracketedTripleExpr);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(496);
		        $this->match(self::T__1);
		        $this->setState(497);
		        $this->tripleExpression();
		        $this->setState(498);
		        $this->match(self::T__2);
		        $this->setState(500);
		        $this->errorHandler->sync($this);
		        $_la = $this->input->LA(1);

		        if ((((($_la - 6)) & ~0x3f) === 0 && ((1 << ($_la - 6)) & ((1 << (self::T__5 - 6)) | (1 << (self::T__10 - 6)) | (1 << (self::T__11 - 6)) | (1 << (self::UNBOUNDED - 6)))) !== 0)) {
		        	$this->setState(499);
		        	$this->cardinality();
		        }
		        $this->setState(505);
		        $this->errorHandler->sync($this);

		        $_la = $this->input->LA(1);
		        while ($_la === self::T__18) {
		        	$this->setState(502);
		        	$this->annotation();
		        	$this->setState(507);
		        	$this->errorHandler->sync($this);
		        	$_la = $this->input->LA(1);
		        }
		        $this->setState(511);
		        $this->errorHandler->sync($this);

		        $_la = $this->input->LA(1);
		        while ($_la === self::T__19) {
		        	$this->setState(508);
		        	$this->semanticAction();
		        	$this->setState(513);
		        	$this->errorHandler->sync($this);
		        	$_la = $this->input->LA(1);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function tripleConstraint() : Context\TripleConstraintContext
		{
		    $localContext = new Context\TripleConstraintContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 94, self::RULE_tripleConstraint);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(515);
		        $this->errorHandler->sync($this);
		        $_la = $this->input->LA(1);

		        if ($_la === self::T__13) {
		        	$this->setState(514);
		        	$this->senseFlags();
		        }
		        $this->setState(517);
		        $this->predicate();
		        $this->setState(518);
		        $this->inlineShapeExpression();
		        $this->setState(520);
		        $this->errorHandler->sync($this);
		        $_la = $this->input->LA(1);

		        if ((((($_la - 6)) & ~0x3f) === 0 && ((1 << ($_la - 6)) & ((1 << (self::T__5 - 6)) | (1 << (self::T__10 - 6)) | (1 << (self::T__11 - 6)) | (1 << (self::UNBOUNDED - 6)))) !== 0)) {
		        	$this->setState(519);
		        	$this->cardinality();
		        }
		        $this->setState(525);
		        $this->errorHandler->sync($this);

		        $_la = $this->input->LA(1);
		        while ($_la === self::T__18) {
		        	$this->setState(522);
		        	$this->annotation();
		        	$this->setState(527);
		        	$this->errorHandler->sync($this);
		        	$_la = $this->input->LA(1);
		        }
		        $this->setState(531);
		        $this->errorHandler->sync($this);

		        $_la = $this->input->LA(1);
		        while ($_la === self::T__19) {
		        	$this->setState(528);
		        	$this->semanticAction();
		        	$this->setState(533);
		        	$this->errorHandler->sync($this);
		        	$_la = $this->input->LA(1);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function cardinality() : Context\CardinalityContext
		{
		    $localContext = new Context\CardinalityContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 96, self::RULE_cardinality);

		    try {
		        $this->setState(538);
		        $this->errorHandler->sync($this);

		        switch ($this->input->LA(1)) {
		            case self::UNBOUNDED:
		            	$localContext = new Context\StarCardinalityContext($localContext);
		            	$this->enterOuterAlt($localContext, 1);
		            	$this->setState(534);
		            	$this->match(self::UNBOUNDED);
		            	break;

		            case self::T__10:
		            	$localContext = new Context\PlusCardinalityContext($localContext);
		            	$this->enterOuterAlt($localContext, 2);
		            	$this->setState(535);
		            	$this->match(self::T__10);
		            	break;

		            case self::T__11:
		            	$localContext = new Context\OptionalCardinalityContext($localContext);
		            	$this->enterOuterAlt($localContext, 3);
		            	$this->setState(536);
		            	$this->match(self::T__11);
		            	break;

		            case self::T__5:
		            	$localContext = new Context\RepeatCardinalityContext($localContext);
		            	$this->enterOuterAlt($localContext, 4);
		            	$this->setState(537);
		            	$this->repeatRange();
		            	break;

		        default:
		        	throw new NoViableAltException($this);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function repeatRange() : Context\RepeatRangeContext
		{
		    $localContext = new Context\RepeatRangeContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 98, self::RULE_repeatRange);

		    try {
		        $this->setState(550);
		        $this->errorHandler->sync($this);

		        switch ($this->getInterpreter()->adaptivePredict($this->input, 67, $this->ctx)) {
		        	case 1:
		        	    $localContext = new Context\ExactRangeContext($localContext);
		        	    $this->enterOuterAlt($localContext, 1);
		        	    $this->setState(540);
		        	    $this->match(self::T__5);
		        	    $this->setState(541);
		        	    $this->match(self::INTEGER);
		        	    $this->setState(542);
		        	    $this->match(self::T__6);
		        	break;

		        	case 2:
		        	    $localContext = new Context\MinMaxRangeContext($localContext);
		        	    $this->enterOuterAlt($localContext, 2);
		        	    $this->setState(543);
		        	    $this->match(self::T__5);
		        	    $this->setState(544);
		        	    $this->match(self::INTEGER);
		        	    $this->setState(545);
		        	    $this->match(self::T__12);
		        	    $this->setState(547);
		        	    $this->errorHandler->sync($this);
		        	    $_la = $this->input->LA(1);

		        	    if ($_la === self::INTEGER || $_la === self::UNBOUNDED) {
		        	    	$this->setState(546);

		        	    	$_la = $this->input->LA(1);

		        	    	if (!($_la === self::INTEGER || $_la === self::UNBOUNDED)) {
		        	    	$this->errorHandler->recoverInline($this);
		        	    	} else {
		        	    		if ($this->input->LA(1) === Token::EOF) {
		        	    		    $this->matchedEOF = true;
		        	    	    }

		        	    		$this->errorHandler->reportMatch($this);
		        	    		$this->consume();
		        	    	}
		        	    }
		        	    $this->setState(549);
		        	    $this->match(self::T__6);
		        	break;
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function senseFlags() : Context\SenseFlagsContext
		{
		    $localContext = new Context\SenseFlagsContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 100, self::RULE_senseFlags);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(552);
		        $this->match(self::T__13);
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function valueSet() : Context\ValueSetContext
		{
		    $localContext = new Context\ValueSetContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 102, self::RULE_valueSet);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(554);
		        $this->match(self::T__14);
		        $this->setState(558);
		        $this->errorHandler->sync($this);

		        $_la = $this->input->LA(1);
		        while (((($_la) & ~0x3f) === 0 && ((1 << $_la) & ((1 << self::T__3) | (1 << self::T__4) | (1 << self::KW_TRUE) | (1 << self::KW_FALSE) | (1 << self::IRIREF) | (1 << self::PNAME_NS) | (1 << self::PNAME_LN) | (1 << self::LANGTAG))) !== 0) || (((($_la - 64)) & ~0x3f) === 0 && ((1 << ($_la - 64)) & ((1 << (self::INTEGER - 64)) | (1 << (self::DECIMAL - 64)) | (1 << (self::DOUBLE - 64)) | (1 << (self::STRING_LITERAL1 - 64)) | (1 << (self::STRING_LITERAL2 - 64)) | (1 << (self::STRING_LITERAL_LONG1 - 64)) | (1 << (self::STRING_LITERAL_LONG2 - 64)))) !== 0)) {
		        	$this->setState(555);
		        	$this->valueSetValue();
		        	$this->setState(560);
		        	$this->errorHandler->sync($this);
		        	$_la = $this->input->LA(1);
		        }
		        $this->setState(561);
		        $this->match(self::T__15);
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function valueSetValue() : Context\ValueSetValueContext
		{
		    $localContext = new Context\ValueSetValueContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 104, self::RULE_valueSetValue);

		    try {
		        $this->setState(584);
		        $this->errorHandler->sync($this);

		        switch ($this->input->LA(1)) {
		            case self::IRIREF:
		            case self::PNAME_NS:
		            case self::PNAME_LN:
		            	$this->enterOuterAlt($localContext, 1);
		            	$this->setState(563);
		            	$this->iriRange();
		            	break;

		            case self::KW_TRUE:
		            case self::KW_FALSE:
		            case self::INTEGER:
		            case self::DECIMAL:
		            case self::DOUBLE:
		            case self::STRING_LITERAL1:
		            case self::STRING_LITERAL2:
		            case self::STRING_LITERAL_LONG1:
		            case self::STRING_LITERAL_LONG2:
		            	$this->enterOuterAlt($localContext, 2);
		            	$this->setState(564);
		            	$this->literalRange();
		            	break;

		            case self::T__4:
		            case self::LANGTAG:
		            	$this->enterOuterAlt($localContext, 3);
		            	$this->setState(565);
		            	$this->languageRange();
		            	break;

		            case self::T__3:
		            	$this->enterOuterAlt($localContext, 4);
		            	$this->setState(566);
		            	$this->match(self::T__3);
		            	$this->setState(582);
		            	$this->errorHandler->sync($this);

		            	switch ($this->getInterpreter()->adaptivePredict($this->input, 72, $this->ctx)) {
		            		case 1:
		            		    $this->setState(568); 
		            		    $this->errorHandler->sync($this);

		            		    $_la = $this->input->LA(1);
		            		    do {
		            		    	$this->setState(567);
		            		    	$this->iriExclusion();
		            		    	$this->setState(570); 
		            		    	$this->errorHandler->sync($this);
		            		    	$_la = $this->input->LA(1);
		            		    } while ($_la === self::T__16);
		            		break;

		            		case 2:
		            		    $this->setState(573); 
		            		    $this->errorHandler->sync($this);

		            		    $_la = $this->input->LA(1);
		            		    do {
		            		    	$this->setState(572);
		            		    	$this->literalExclusion();
		            		    	$this->setState(575); 
		            		    	$this->errorHandler->sync($this);
		            		    	$_la = $this->input->LA(1);
		            		    } while ($_la === self::T__16);
		            		break;

		            		case 3:
		            		    $this->setState(578); 
		            		    $this->errorHandler->sync($this);

		            		    $_la = $this->input->LA(1);
		            		    do {
		            		    	$this->setState(577);
		            		    	$this->languageExclusion();
		            		    	$this->setState(580); 
		            		    	$this->errorHandler->sync($this);
		            		    	$_la = $this->input->LA(1);
		            		    } while ($_la === self::T__16);
		            		break;
		            	}
		            	break;

		        default:
		        	throw new NoViableAltException($this);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function iriRange() : Context\IriRangeContext
		{
		    $localContext = new Context\IriRangeContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 106, self::RULE_iriRange);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(586);
		        $this->iri();
		        $this->setState(594);
		        $this->errorHandler->sync($this);
		        $_la = $this->input->LA(1);

		        if ($_la === self::STEM_MARK) {
		        	$this->setState(587);
		        	$this->match(self::STEM_MARK);
		        	$this->setState(591);
		        	$this->errorHandler->sync($this);

		        	$_la = $this->input->LA(1);
		        	while ($_la === self::T__16) {
		        		$this->setState(588);
		        		$this->iriExclusion();
		        		$this->setState(593);
		        		$this->errorHandler->sync($this);
		        		$_la = $this->input->LA(1);
		        	}
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function iriExclusion() : Context\IriExclusionContext
		{
		    $localContext = new Context\IriExclusionContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 108, self::RULE_iriExclusion);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(596);
		        $this->match(self::T__16);
		        $this->setState(597);
		        $this->iri();
		        $this->setState(599);
		        $this->errorHandler->sync($this);
		        $_la = $this->input->LA(1);

		        if ($_la === self::STEM_MARK) {
		        	$this->setState(598);
		        	$this->match(self::STEM_MARK);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function literalRange() : Context\LiteralRangeContext
		{
		    $localContext = new Context\LiteralRangeContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 110, self::RULE_literalRange);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(601);
		        $this->literal();
		        $this->setState(609);
		        $this->errorHandler->sync($this);
		        $_la = $this->input->LA(1);

		        if ($_la === self::STEM_MARK) {
		        	$this->setState(602);
		        	$this->match(self::STEM_MARK);
		        	$this->setState(606);
		        	$this->errorHandler->sync($this);

		        	$_la = $this->input->LA(1);
		        	while ($_la === self::T__16) {
		        		$this->setState(603);
		        		$this->literalExclusion();
		        		$this->setState(608);
		        		$this->errorHandler->sync($this);
		        		$_la = $this->input->LA(1);
		        	}
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function literalExclusion() : Context\LiteralExclusionContext
		{
		    $localContext = new Context\LiteralExclusionContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 112, self::RULE_literalExclusion);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(611);
		        $this->match(self::T__16);
		        $this->setState(612);
		        $this->literal();
		        $this->setState(614);
		        $this->errorHandler->sync($this);
		        $_la = $this->input->LA(1);

		        if ($_la === self::STEM_MARK) {
		        	$this->setState(613);
		        	$this->match(self::STEM_MARK);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function languageRange() : Context\LanguageRangeContext
		{
		    $localContext = new Context\LanguageRangeContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 114, self::RULE_languageRange);

		    try {
		        $this->setState(634);
		        $this->errorHandler->sync($this);

		        switch ($this->input->LA(1)) {
		            case self::LANGTAG:
		            	$localContext = new Context\LanguageRangeFullContext($localContext);
		            	$this->enterOuterAlt($localContext, 1);
		            	$this->setState(616);
		            	$this->match(self::LANGTAG);
		            	$this->setState(624);
		            	$this->errorHandler->sync($this);
		            	$_la = $this->input->LA(1);

		            	if ($_la === self::STEM_MARK) {
		            		$this->setState(617);
		            		$this->match(self::STEM_MARK);
		            		$this->setState(621);
		            		$this->errorHandler->sync($this);

		            		$_la = $this->input->LA(1);
		            		while ($_la === self::T__16) {
		            			$this->setState(618);
		            			$this->languageExclusion();
		            			$this->setState(623);
		            			$this->errorHandler->sync($this);
		            			$_la = $this->input->LA(1);
		            		}
		            	}
		            	break;

		            case self::T__4:
		            	$localContext = new Context\LanguageRangeAtContext($localContext);
		            	$this->enterOuterAlt($localContext, 2);
		            	$this->setState(626);
		            	$this->match(self::T__4);
		            	$this->setState(627);
		            	$this->match(self::STEM_MARK);
		            	$this->setState(631);
		            	$this->errorHandler->sync($this);

		            	$_la = $this->input->LA(1);
		            	while ($_la === self::T__16) {
		            		$this->setState(628);
		            		$this->languageExclusion();
		            		$this->setState(633);
		            		$this->errorHandler->sync($this);
		            		$_la = $this->input->LA(1);
		            	}
		            	break;

		        default:
		        	throw new NoViableAltException($this);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function languageExclusion() : Context\LanguageExclusionContext
		{
		    $localContext = new Context\LanguageExclusionContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 116, self::RULE_languageExclusion);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(636);
		        $this->match(self::T__16);
		        $this->setState(637);
		        $this->match(self::LANGTAG);
		        $this->setState(639);
		        $this->errorHandler->sync($this);
		        $_la = $this->input->LA(1);

		        if ($_la === self::STEM_MARK) {
		        	$this->setState(638);
		        	$this->match(self::STEM_MARK);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function r_include() : Context\R_includeContext
		{
		    $localContext = new Context\R_includeContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 118, self::RULE_r_include);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(641);
		        $this->match(self::T__17);
		        $this->setState(642);
		        $this->tripleExprLabel();
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function annotation() : Context\AnnotationContext
		{
		    $localContext = new Context\AnnotationContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 120, self::RULE_annotation);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(644);
		        $this->match(self::T__18);
		        $this->setState(645);
		        $this->predicate();
		        $this->setState(648);
		        $this->errorHandler->sync($this);

		        switch ($this->input->LA(1)) {
		            case self::IRIREF:
		            case self::PNAME_NS:
		            case self::PNAME_LN:
		            	$this->setState(646);
		            	$this->iri();
		            	break;

		            case self::KW_TRUE:
		            case self::KW_FALSE:
		            case self::INTEGER:
		            case self::DECIMAL:
		            case self::DOUBLE:
		            case self::STRING_LITERAL1:
		            case self::STRING_LITERAL2:
		            case self::STRING_LITERAL_LONG1:
		            case self::STRING_LITERAL_LONG2:
		            	$this->setState(647);
		            	$this->literal();
		            	break;

		        default:
		        	throw new NoViableAltException($this);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function semanticAction() : Context\SemanticActionContext
		{
		    $localContext = new Context\SemanticActionContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 122, self::RULE_semanticAction);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(650);
		        $this->match(self::T__19);
		        $this->setState(651);
		        $this->iri();
		        $this->setState(652);

		        $_la = $this->input->LA(1);

		        if (!($_la === self::T__19 || $_la === self::CODE)) {
		        $this->errorHandler->recoverInline($this);
		        } else {
		        	if ($this->input->LA(1) === Token::EOF) {
		        	    $this->matchedEOF = true;
		            }

		        	$this->errorHandler->reportMatch($this);
		        	$this->consume();
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function literal() : Context\LiteralContext
		{
		    $localContext = new Context\LiteralContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 124, self::RULE_literal);

		    try {
		        $this->setState(657);
		        $this->errorHandler->sync($this);

		        switch ($this->input->LA(1)) {
		            case self::STRING_LITERAL1:
		            case self::STRING_LITERAL2:
		            case self::STRING_LITERAL_LONG1:
		            case self::STRING_LITERAL_LONG2:
		            	$this->enterOuterAlt($localContext, 1);
		            	$this->setState(654);
		            	$this->rdfLiteral();
		            	break;

		            case self::INTEGER:
		            case self::DECIMAL:
		            case self::DOUBLE:
		            	$this->enterOuterAlt($localContext, 2);
		            	$this->setState(655);
		            	$this->numericLiteral();
		            	break;

		            case self::KW_TRUE:
		            case self::KW_FALSE:
		            	$this->enterOuterAlt($localContext, 3);
		            	$this->setState(656);
		            	$this->booleanLiteral();
		            	break;

		        default:
		        	throw new NoViableAltException($this);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function predicate() : Context\PredicateContext
		{
		    $localContext = new Context\PredicateContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 126, self::RULE_predicate);

		    try {
		        $this->setState(661);
		        $this->errorHandler->sync($this);

		        switch ($this->input->LA(1)) {
		            case self::IRIREF:
		            case self::PNAME_NS:
		            case self::PNAME_LN:
		            	$this->enterOuterAlt($localContext, 1);
		            	$this->setState(659);
		            	$this->iri();
		            	break;

		            case self::RDF_TYPE:
		            	$this->enterOuterAlt($localContext, 2);
		            	$this->setState(660);
		            	$this->rdfType();
		            	break;

		        default:
		        	throw new NoViableAltException($this);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function rdfType() : Context\RdfTypeContext
		{
		    $localContext = new Context\RdfTypeContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 128, self::RULE_rdfType);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(663);
		        $this->match(self::RDF_TYPE);
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function datatype() : Context\DatatypeContext
		{
		    $localContext = new Context\DatatypeContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 130, self::RULE_datatype);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(665);
		        $this->iri();
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function shapeExprLabel() : Context\ShapeExprLabelContext
		{
		    $localContext = new Context\ShapeExprLabelContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 132, self::RULE_shapeExprLabel);

		    try {
		        $this->setState(669);
		        $this->errorHandler->sync($this);

		        switch ($this->input->LA(1)) {
		            case self::IRIREF:
		            case self::PNAME_NS:
		            case self::PNAME_LN:
		            	$this->enterOuterAlt($localContext, 1);
		            	$this->setState(667);
		            	$this->iri();
		            	break;

		            case self::BLANK_NODE_LABEL:
		            	$this->enterOuterAlt($localContext, 2);
		            	$this->setState(668);
		            	$this->blankNode();
		            	break;

		        default:
		        	throw new NoViableAltException($this);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function tripleExprLabel() : Context\TripleExprLabelContext
		{
		    $localContext = new Context\TripleExprLabelContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 134, self::RULE_tripleExprLabel);

		    try {
		        $this->setState(673);
		        $this->errorHandler->sync($this);

		        switch ($this->input->LA(1)) {
		            case self::IRIREF:
		            case self::PNAME_NS:
		            case self::PNAME_LN:
		            	$this->enterOuterAlt($localContext, 1);
		            	$this->setState(671);
		            	$this->iri();
		            	break;

		            case self::BLANK_NODE_LABEL:
		            	$this->enterOuterAlt($localContext, 2);
		            	$this->setState(672);
		            	$this->blankNode();
		            	break;

		        default:
		        	throw new NoViableAltException($this);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function numericLiteral() : Context\NumericLiteralContext
		{
		    $localContext = new Context\NumericLiteralContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 136, self::RULE_numericLiteral);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(675);

		        $_la = $this->input->LA(1);

		        if (!((((($_la - 64)) & ~0x3f) === 0 && ((1 << ($_la - 64)) & ((1 << (self::INTEGER - 64)) | (1 << (self::DECIMAL - 64)) | (1 << (self::DOUBLE - 64)))) !== 0))) {
		        $this->errorHandler->recoverInline($this);
		        } else {
		        	if ($this->input->LA(1) === Token::EOF) {
		        	    $this->matchedEOF = true;
		            }

		        	$this->errorHandler->reportMatch($this);
		        	$this->consume();
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function rdfLiteral() : Context\RdfLiteralContext
		{
		    $localContext = new Context\RdfLiteralContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 138, self::RULE_rdfLiteral);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(677);
		        $this->string();
		        $this->setState(681);
		        $this->errorHandler->sync($this);

		        switch ($this->getInterpreter()->adaptivePredict($this->input, 90, $this->ctx)) {
		            case 1:
		        	    $this->setState(678);
		        	    $this->match(self::LANGTAG);
		        	break;

		            case 2:
		        	    $this->setState(679);
		        	    $this->match(self::T__20);
		        	    $this->setState(680);
		        	    $this->datatype();
		        	break;
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function booleanLiteral() : Context\BooleanLiteralContext
		{
		    $localContext = new Context\BooleanLiteralContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 140, self::RULE_booleanLiteral);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(683);

		        $_la = $this->input->LA(1);

		        if (!($_la === self::KW_TRUE || $_la === self::KW_FALSE)) {
		        $this->errorHandler->recoverInline($this);
		        } else {
		        	if ($this->input->LA(1) === Token::EOF) {
		        	    $this->matchedEOF = true;
		            }

		        	$this->errorHandler->reportMatch($this);
		        	$this->consume();
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function string() : Context\StringContext
		{
		    $localContext = new Context\StringContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 142, self::RULE_string);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(685);

		        $_la = $this->input->LA(1);

		        if (!((((($_la - 69)) & ~0x3f) === 0 && ((1 << ($_la - 69)) & ((1 << (self::STRING_LITERAL1 - 69)) | (1 << (self::STRING_LITERAL2 - 69)) | (1 << (self::STRING_LITERAL_LONG1 - 69)) | (1 << (self::STRING_LITERAL_LONG2 - 69)))) !== 0))) {
		        $this->errorHandler->recoverInline($this);
		        } else {
		        	if ($this->input->LA(1) === Token::EOF) {
		        	    $this->matchedEOF = true;
		            }

		        	$this->errorHandler->reportMatch($this);
		        	$this->consume();
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function iri() : Context\IriContext
		{
		    $localContext = new Context\IriContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 144, self::RULE_iri);

		    try {
		        $this->setState(689);
		        $this->errorHandler->sync($this);

		        switch ($this->input->LA(1)) {
		            case self::IRIREF:
		            	$this->enterOuterAlt($localContext, 1);
		            	$this->setState(687);
		            	$this->match(self::IRIREF);
		            	break;

		            case self::PNAME_NS:
		            case self::PNAME_LN:
		            	$this->enterOuterAlt($localContext, 2);
		            	$this->setState(688);
		            	$this->prefixedName();
		            	break;

		        default:
		        	throw new NoViableAltException($this);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function prefixedName() : Context\PrefixedNameContext
		{
		    $localContext = new Context\PrefixedNameContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 146, self::RULE_prefixedName);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(691);

		        $_la = $this->input->LA(1);

		        if (!($_la === self::PNAME_NS || $_la === self::PNAME_LN)) {
		        $this->errorHandler->recoverInline($this);
		        } else {
		        	if ($this->input->LA(1) === Token::EOF) {
		        	    $this->matchedEOF = true;
		            }

		        	$this->errorHandler->reportMatch($this);
		        	$this->consume();
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function blankNode() : Context\BlankNodeContext
		{
		    $localContext = new Context\BlankNodeContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 148, self::RULE_blankNode);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(693);
		        $this->match(self::BLANK_NODE_LABEL);
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function extension() : Context\ExtensionContext
		{
		    $localContext = new Context\ExtensionContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 150, self::RULE_extension);

		    try {
		        $this->setState(699);
		        $this->errorHandler->sync($this);

		        switch ($this->input->LA(1)) {
		            case self::KW_EXTENDS:
		            	$this->enterOuterAlt($localContext, 1);
		            	$this->setState(695);
		            	$this->match(self::KW_EXTENDS);
		            	$this->setState(696);
		            	$this->shapeExprLabel();
		            	break;

		            case self::T__17:
		            	$this->enterOuterAlt($localContext, 2);
		            	$this->setState(697);
		            	$this->match(self::T__17);
		            	$this->setState(698);
		            	$this->shapeExprLabel();
		            	break;

		        default:
		        	throw new NoViableAltException($this);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function restrictions() : Context\RestrictionsContext
		{
		    $localContext = new Context\RestrictionsContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 152, self::RULE_restrictions);

		    try {
		        $this->setState(705);
		        $this->errorHandler->sync($this);

		        switch ($this->input->LA(1)) {
		            case self::KW_RESTRICTS:
		            	$this->enterOuterAlt($localContext, 1);
		            	$this->setState(701);
		            	$this->match(self::KW_RESTRICTS);
		            	$this->setState(702);
		            	$this->shapeExprLabel();
		            	break;

		            case self::T__16:
		            	$this->enterOuterAlt($localContext, 2);
		            	$this->setState(703);
		            	$this->match(self::T__16);
		            	$this->setState(704);
		            	$this->shapeExprLabel();
		            	break;

		        default:
		        	throw new NoViableAltException($this);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}
	}
}

namespace com_brucemyers\ShEx\ShExDoc\Context {
	use Antlr\Antlr4\Runtime\ParserRuleContext;
	use Antlr\Antlr4\Runtime\Token;
	use Antlr\Antlr4\Runtime\Tree\ParseTreeVisitor;
	use Antlr\Antlr4\Runtime\Tree\TerminalNode;
	use Antlr\Antlr4\Runtime\Tree\ParseTreeListener;
	use com_brucemyers\ShEx\ShExDoc\ShExDocParser;
	use com_brucemyers\ShEx\ShExDoc\ShExDocListener;

	class ShExDocContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_shExDoc;
	    }

	    public function EOF() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::EOF, 0);
	    }

	    /**
	     * @return array<DirectiveContext>|DirectiveContext|null
	     */
	    public function directive(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTypedRuleContexts(DirectiveContext::class);
	    	}

	        return $this->getTypedRuleContext(DirectiveContext::class, $index);
	    }

	    public function notStartAction() : ?NotStartActionContext
	    {
	    	return $this->getTypedRuleContext(NotStartActionContext::class, 0);
	    }

	    public function startActions() : ?StartActionsContext
	    {
	    	return $this->getTypedRuleContext(StartActionsContext::class, 0);
	    }

	    /**
	     * @return array<StatementContext>|StatementContext|null
	     */
	    public function statement(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTypedRuleContexts(StatementContext::class);
	    	}

	        return $this->getTypedRuleContext(StatementContext::class, $index);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterShExDoc($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitShExDoc($this);
		    }
		}
	} 

	class DirectiveContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_directive;
	    }

	    public function baseDecl() : ?BaseDeclContext
	    {
	    	return $this->getTypedRuleContext(BaseDeclContext::class, 0);
	    }

	    public function prefixDecl() : ?PrefixDeclContext
	    {
	    	return $this->getTypedRuleContext(PrefixDeclContext::class, 0);
	    }

	    public function importDecl() : ?ImportDeclContext
	    {
	    	return $this->getTypedRuleContext(ImportDeclContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterDirective($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitDirective($this);
		    }
		}
	} 

	class BaseDeclContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_baseDecl;
	    }

	    public function KW_BASE() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::KW_BASE, 0);
	    }

	    public function IRIREF() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::IRIREF, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterBaseDecl($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitBaseDecl($this);
		    }
		}
	} 

	class PrefixDeclContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_prefixDecl;
	    }

	    public function KW_PREFIX() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::KW_PREFIX, 0);
	    }

	    public function PNAME_NS() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::PNAME_NS, 0);
	    }

	    public function IRIREF() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::IRIREF, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterPrefixDecl($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitPrefixDecl($this);
		    }
		}
	} 

	class ImportDeclContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_importDecl;
	    }

	    public function KW_IMPORT() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::KW_IMPORT, 0);
	    }

	    public function IRIREF() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::IRIREF, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterImportDecl($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitImportDecl($this);
		    }
		}
	} 

	class NotStartActionContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_notStartAction;
	    }

	    public function start() : ?StartContext
	    {
	    	return $this->getTypedRuleContext(StartContext::class, 0);
	    }

	    public function shapeExprDecl() : ?ShapeExprDeclContext
	    {
	    	return $this->getTypedRuleContext(ShapeExprDeclContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterNotStartAction($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitNotStartAction($this);
		    }
		}
	} 

	class StartContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_start;
	    }

	    public function KW_START() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::KW_START, 0);
	    }

	    public function shapeExpression() : ?ShapeExpressionContext
	    {
	    	return $this->getTypedRuleContext(ShapeExpressionContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterStart($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitStart($this);
		    }
		}
	} 

	class StartActionsContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_startActions;
	    }

	    /**
	     * @return array<SemanticActionContext>|SemanticActionContext|null
	     */
	    public function semanticAction(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTypedRuleContexts(SemanticActionContext::class);
	    	}

	        return $this->getTypedRuleContext(SemanticActionContext::class, $index);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterStartActions($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitStartActions($this);
		    }
		}
	} 

	class StatementContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_statement;
	    }

	    public function directive() : ?DirectiveContext
	    {
	    	return $this->getTypedRuleContext(DirectiveContext::class, 0);
	    }

	    public function notStartAction() : ?NotStartActionContext
	    {
	    	return $this->getTypedRuleContext(NotStartActionContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterStatement($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitStatement($this);
		    }
		}
	} 

	class ShapeExprDeclContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_shapeExprDecl;
	    }

	    public function shapeExprLabel() : ?ShapeExprLabelContext
	    {
	    	return $this->getTypedRuleContext(ShapeExprLabelContext::class, 0);
	    }

	    public function shapeExpression() : ?ShapeExpressionContext
	    {
	    	return $this->getTypedRuleContext(ShapeExpressionContext::class, 0);
	    }

	    public function KW_EXTERNAL() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::KW_EXTERNAL, 0);
	    }

	    public function KW_ABSTRACT() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::KW_ABSTRACT, 0);
	    }

	    /**
	     * @return array<RestrictionsContext>|RestrictionsContext|null
	     */
	    public function restrictions(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTypedRuleContexts(RestrictionsContext::class);
	    	}

	        return $this->getTypedRuleContext(RestrictionsContext::class, $index);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterShapeExprDecl($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitShapeExprDecl($this);
		    }
		}
	} 

	class ShapeExpressionContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_shapeExpression;
	    }

	    public function shapeOr() : ?ShapeOrContext
	    {
	    	return $this->getTypedRuleContext(ShapeOrContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterShapeExpression($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitShapeExpression($this);
		    }
		}
	} 

	class InlineShapeExpressionContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_inlineShapeExpression;
	    }

	    public function inlineShapeOr() : ?InlineShapeOrContext
	    {
	    	return $this->getTypedRuleContext(InlineShapeOrContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterInlineShapeExpression($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitInlineShapeExpression($this);
		    }
		}
	} 

	class ShapeOrContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_shapeOr;
	    }

	    /**
	     * @return array<ShapeAndContext>|ShapeAndContext|null
	     */
	    public function shapeAnd(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTypedRuleContexts(ShapeAndContext::class);
	    	}

	        return $this->getTypedRuleContext(ShapeAndContext::class, $index);
	    }

	    /**
	     * @return array<TerminalNode>|TerminalNode|null
	     */
	    public function KW_OR(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTokens(ShExDocParser::KW_OR);
	    	}

	        return $this->getToken(ShExDocParser::KW_OR, $index);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterShapeOr($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitShapeOr($this);
		    }
		}
	} 

	class InlineShapeOrContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_inlineShapeOr;
	    }

	    /**
	     * @return array<InlineShapeAndContext>|InlineShapeAndContext|null
	     */
	    public function inlineShapeAnd(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTypedRuleContexts(InlineShapeAndContext::class);
	    	}

	        return $this->getTypedRuleContext(InlineShapeAndContext::class, $index);
	    }

	    /**
	     * @return array<TerminalNode>|TerminalNode|null
	     */
	    public function KW_OR(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTokens(ShExDocParser::KW_OR);
	    	}

	        return $this->getToken(ShExDocParser::KW_OR, $index);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterInlineShapeOr($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitInlineShapeOr($this);
		    }
		}
	} 

	class ShapeAndContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_shapeAnd;
	    }

	    /**
	     * @return array<ShapeNotContext>|ShapeNotContext|null
	     */
	    public function shapeNot(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTypedRuleContexts(ShapeNotContext::class);
	    	}

	        return $this->getTypedRuleContext(ShapeNotContext::class, $index);
	    }

	    /**
	     * @return array<TerminalNode>|TerminalNode|null
	     */
	    public function KW_AND(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTokens(ShExDocParser::KW_AND);
	    	}

	        return $this->getToken(ShExDocParser::KW_AND, $index);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterShapeAnd($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitShapeAnd($this);
		    }
		}
	} 

	class InlineShapeAndContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_inlineShapeAnd;
	    }

	    /**
	     * @return array<InlineShapeNotContext>|InlineShapeNotContext|null
	     */
	    public function inlineShapeNot(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTypedRuleContexts(InlineShapeNotContext::class);
	    	}

	        return $this->getTypedRuleContext(InlineShapeNotContext::class, $index);
	    }

	    /**
	     * @return array<TerminalNode>|TerminalNode|null
	     */
	    public function KW_AND(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTokens(ShExDocParser::KW_AND);
	    	}

	        return $this->getToken(ShExDocParser::KW_AND, $index);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterInlineShapeAnd($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitInlineShapeAnd($this);
		    }
		}
	} 

	class ShapeNotContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_shapeNot;
	    }

	    public function shapeAtom() : ?ShapeAtomContext
	    {
	    	return $this->getTypedRuleContext(ShapeAtomContext::class, 0);
	    }

	    public function KW_NOT() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::KW_NOT, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterShapeNot($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitShapeNot($this);
		    }
		}
	} 

	class InlineShapeNotContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_inlineShapeNot;
	    }

	    public function inlineShapeAtom() : ?InlineShapeAtomContext
	    {
	    	return $this->getTypedRuleContext(InlineShapeAtomContext::class, 0);
	    }

	    public function KW_NOT() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::KW_NOT, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterInlineShapeNot($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitInlineShapeNot($this);
		    }
		}
	} 

	class ShapeAtomContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_shapeAtom;
	    }
	 
		public function copyFrom(ParserRuleContext $context) : void
		{
			parent::copyFrom($context);

		}
	}

	class ShapeAtomShapeOrRefContext extends ShapeAtomContext
	{
		public function __construct(ShapeAtomContext $context)
		{
		    parent::__construct($context);

		    $this->copyFrom($context);
	    }

	    public function shapeOrRef() : ?ShapeOrRefContext
	    {
	    	return $this->getTypedRuleContext(ShapeOrRefContext::class, 0);
	    }

	    public function nonLitNodeConstraint() : ?NonLitNodeConstraintContext
	    {
	    	return $this->getTypedRuleContext(NonLitNodeConstraintContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterShapeAtomShapeOrRef($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitShapeAtomShapeOrRef($this);
		    }
		}
	}

	class ShapeAtomNonLitNodeConstraintContext extends ShapeAtomContext
	{
		public function __construct(ShapeAtomContext $context)
		{
		    parent::__construct($context);

		    $this->copyFrom($context);
	    }

	    public function nonLitNodeConstraint() : ?NonLitNodeConstraintContext
	    {
	    	return $this->getTypedRuleContext(NonLitNodeConstraintContext::class, 0);
	    }

	    public function shapeOrRef() : ?ShapeOrRefContext
	    {
	    	return $this->getTypedRuleContext(ShapeOrRefContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterShapeAtomNonLitNodeConstraint($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitShapeAtomNonLitNodeConstraint($this);
		    }
		}
	}

	class ShapeAtomLitNodeConstraintContext extends ShapeAtomContext
	{
		public function __construct(ShapeAtomContext $context)
		{
		    parent::__construct($context);

		    $this->copyFrom($context);
	    }

	    public function litNodeConstraint() : ?LitNodeConstraintContext
	    {
	    	return $this->getTypedRuleContext(LitNodeConstraintContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterShapeAtomLitNodeConstraint($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitShapeAtomLitNodeConstraint($this);
		    }
		}
	}

	class ShapeAtomShapeExpressionContext extends ShapeAtomContext
	{
		public function __construct(ShapeAtomContext $context)
		{
		    parent::__construct($context);

		    $this->copyFrom($context);
	    }

	    public function shapeExpression() : ?ShapeExpressionContext
	    {
	    	return $this->getTypedRuleContext(ShapeExpressionContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterShapeAtomShapeExpression($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitShapeAtomShapeExpression($this);
		    }
		}
	}

	class ShapeAtomAnyContext extends ShapeAtomContext
	{
		public function __construct(ShapeAtomContext $context)
		{
		    parent::__construct($context);

		    $this->copyFrom($context);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterShapeAtomAny($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitShapeAtomAny($this);
		    }
		}
	} 

	class InlineShapeAtomContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_inlineShapeAtom;
	    }
	 
		public function copyFrom(ParserRuleContext $context) : void
		{
			parent::copyFrom($context);

		}
	}

	class InlineShapeAtomShapeExpressionContext extends InlineShapeAtomContext
	{
		public function __construct(InlineShapeAtomContext $context)
		{
		    parent::__construct($context);

		    $this->copyFrom($context);
	    }

	    public function shapeExpression() : ?ShapeExpressionContext
	    {
	    	return $this->getTypedRuleContext(ShapeExpressionContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterInlineShapeAtomShapeExpression($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitInlineShapeAtomShapeExpression($this);
		    }
		}
	}

	class InlineShapeAtomLitNodeConstraintContext extends InlineShapeAtomContext
	{
		public function __construct(InlineShapeAtomContext $context)
		{
		    parent::__construct($context);

		    $this->copyFrom($context);
	    }

	    public function inlineLitNodeConstraint() : ?InlineLitNodeConstraintContext
	    {
	    	return $this->getTypedRuleContext(InlineLitNodeConstraintContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterInlineShapeAtomLitNodeConstraint($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitInlineShapeAtomLitNodeConstraint($this);
		    }
		}
	}

	class InlineShapeAtomShapeOrRefContext extends InlineShapeAtomContext
	{
		public function __construct(InlineShapeAtomContext $context)
		{
		    parent::__construct($context);

		    $this->copyFrom($context);
	    }

	    public function inlineShapeOrRef() : ?InlineShapeOrRefContext
	    {
	    	return $this->getTypedRuleContext(InlineShapeOrRefContext::class, 0);
	    }

	    public function inlineNonLitNodeConstraint() : ?InlineNonLitNodeConstraintContext
	    {
	    	return $this->getTypedRuleContext(InlineNonLitNodeConstraintContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterInlineShapeAtomShapeOrRef($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitInlineShapeAtomShapeOrRef($this);
		    }
		}
	}

	class InlineShapeAtomAnyContext extends InlineShapeAtomContext
	{
		public function __construct(InlineShapeAtomContext $context)
		{
		    parent::__construct($context);

		    $this->copyFrom($context);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterInlineShapeAtomAny($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitInlineShapeAtomAny($this);
		    }
		}
	}

	class InlineShapeAtomNonLitNodeConstraintContext extends InlineShapeAtomContext
	{
		public function __construct(InlineShapeAtomContext $context)
		{
		    parent::__construct($context);

		    $this->copyFrom($context);
	    }

	    public function inlineNonLitNodeConstraint() : ?InlineNonLitNodeConstraintContext
	    {
	    	return $this->getTypedRuleContext(InlineNonLitNodeConstraintContext::class, 0);
	    }

	    public function inlineShapeOrRef() : ?InlineShapeOrRefContext
	    {
	    	return $this->getTypedRuleContext(InlineShapeOrRefContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterInlineShapeAtomNonLitNodeConstraint($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitInlineShapeAtomNonLitNodeConstraint($this);
		    }
		}
	} 

	class ShapeOrRefContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_shapeOrRef;
	    }

	    public function shapeDefinition() : ?ShapeDefinitionContext
	    {
	    	return $this->getTypedRuleContext(ShapeDefinitionContext::class, 0);
	    }

	    public function shapeRef() : ?ShapeRefContext
	    {
	    	return $this->getTypedRuleContext(ShapeRefContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterShapeOrRef($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitShapeOrRef($this);
		    }
		}
	} 

	class InlineShapeOrRefContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_inlineShapeOrRef;
	    }

	    public function inlineShapeDefinition() : ?InlineShapeDefinitionContext
	    {
	    	return $this->getTypedRuleContext(InlineShapeDefinitionContext::class, 0);
	    }

	    public function shapeRef() : ?ShapeRefContext
	    {
	    	return $this->getTypedRuleContext(ShapeRefContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterInlineShapeOrRef($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitInlineShapeOrRef($this);
		    }
		}
	} 

	class ShapeRefContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_shapeRef;
	    }

	    public function ATPNAME_LN() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::ATPNAME_LN, 0);
	    }

	    public function ATPNAME_NS() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::ATPNAME_NS, 0);
	    }

	    public function shapeExprLabel() : ?ShapeExprLabelContext
	    {
	    	return $this->getTypedRuleContext(ShapeExprLabelContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterShapeRef($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitShapeRef($this);
		    }
		}
	} 

	class InlineLitNodeConstraintContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_inlineLitNodeConstraint;
	    }
	 
		public function copyFrom(ParserRuleContext $context) : void
		{
			parent::copyFrom($context);

		}
	}

	class NodeConstraintNumericFacetContext extends InlineLitNodeConstraintContext
	{
		public function __construct(InlineLitNodeConstraintContext $context)
		{
		    parent::__construct($context);

		    $this->copyFrom($context);
	    }

	    /**
	     * @return array<NumericFacetContext>|NumericFacetContext|null
	     */
	    public function numericFacet(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTypedRuleContexts(NumericFacetContext::class);
	    	}

	        return $this->getTypedRuleContext(NumericFacetContext::class, $index);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterNodeConstraintNumericFacet($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitNodeConstraintNumericFacet($this);
		    }
		}
	}

	class NodeConstraintLiteralContext extends InlineLitNodeConstraintContext
	{
		public function __construct(InlineLitNodeConstraintContext $context)
		{
		    parent::__construct($context);

		    $this->copyFrom($context);
	    }

	    public function KW_LITERAL() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::KW_LITERAL, 0);
	    }

	    /**
	     * @return array<XsFacetContext>|XsFacetContext|null
	     */
	    public function xsFacet(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTypedRuleContexts(XsFacetContext::class);
	    	}

	        return $this->getTypedRuleContext(XsFacetContext::class, $index);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterNodeConstraintLiteral($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitNodeConstraintLiteral($this);
		    }
		}
	}

	class NodeConstraintNonLiteralContext extends InlineLitNodeConstraintContext
	{
		public function __construct(InlineLitNodeConstraintContext $context)
		{
		    parent::__construct($context);

		    $this->copyFrom($context);
	    }

	    public function nonLiteralKind() : ?NonLiteralKindContext
	    {
	    	return $this->getTypedRuleContext(NonLiteralKindContext::class, 0);
	    }

	    /**
	     * @return array<StringFacetContext>|StringFacetContext|null
	     */
	    public function stringFacet(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTypedRuleContexts(StringFacetContext::class);
	    	}

	        return $this->getTypedRuleContext(StringFacetContext::class, $index);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterNodeConstraintNonLiteral($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitNodeConstraintNonLiteral($this);
		    }
		}
	}

	class NodeConstraintDatatypeContext extends InlineLitNodeConstraintContext
	{
		public function __construct(InlineLitNodeConstraintContext $context)
		{
		    parent::__construct($context);

		    $this->copyFrom($context);
	    }

	    public function datatype() : ?DatatypeContext
	    {
	    	return $this->getTypedRuleContext(DatatypeContext::class, 0);
	    }

	    /**
	     * @return array<XsFacetContext>|XsFacetContext|null
	     */
	    public function xsFacet(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTypedRuleContexts(XsFacetContext::class);
	    	}

	        return $this->getTypedRuleContext(XsFacetContext::class, $index);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterNodeConstraintDatatype($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitNodeConstraintDatatype($this);
		    }
		}
	}

	class NodeConstraintValueSetContext extends InlineLitNodeConstraintContext
	{
		public function __construct(InlineLitNodeConstraintContext $context)
		{
		    parent::__construct($context);

		    $this->copyFrom($context);
	    }

	    public function valueSet() : ?ValueSetContext
	    {
	    	return $this->getTypedRuleContext(ValueSetContext::class, 0);
	    }

	    /**
	     * @return array<XsFacetContext>|XsFacetContext|null
	     */
	    public function xsFacet(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTypedRuleContexts(XsFacetContext::class);
	    	}

	        return $this->getTypedRuleContext(XsFacetContext::class, $index);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterNodeConstraintValueSet($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitNodeConstraintValueSet($this);
		    }
		}
	} 

	class LitNodeConstraintContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_litNodeConstraint;
	    }

	    public function inlineLitNodeConstraint() : ?InlineLitNodeConstraintContext
	    {
	    	return $this->getTypedRuleContext(InlineLitNodeConstraintContext::class, 0);
	    }

	    /**
	     * @return array<AnnotationContext>|AnnotationContext|null
	     */
	    public function annotation(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTypedRuleContexts(AnnotationContext::class);
	    	}

	        return $this->getTypedRuleContext(AnnotationContext::class, $index);
	    }

	    /**
	     * @return array<SemanticActionContext>|SemanticActionContext|null
	     */
	    public function semanticAction(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTypedRuleContexts(SemanticActionContext::class);
	    	}

	        return $this->getTypedRuleContext(SemanticActionContext::class, $index);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterLitNodeConstraint($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitLitNodeConstraint($this);
		    }
		}
	} 

	class InlineNonLitNodeConstraintContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_inlineNonLitNodeConstraint;
	    }
	 
		public function copyFrom(ParserRuleContext $context) : void
		{
			parent::copyFrom($context);

		}
	}

	class LitNodeConstraintStringFacetContext extends InlineNonLitNodeConstraintContext
	{
		public function __construct(InlineNonLitNodeConstraintContext $context)
		{
		    parent::__construct($context);

		    $this->copyFrom($context);
	    }

	    /**
	     * @return array<StringFacetContext>|StringFacetContext|null
	     */
	    public function stringFacet(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTypedRuleContexts(StringFacetContext::class);
	    	}

	        return $this->getTypedRuleContext(StringFacetContext::class, $index);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterLitNodeConstraintStringFacet($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitLitNodeConstraintStringFacet($this);
		    }
		}
	}

	class LitNodeConstraintLiteralContext extends InlineNonLitNodeConstraintContext
	{
		public function __construct(InlineNonLitNodeConstraintContext $context)
		{
		    parent::__construct($context);

		    $this->copyFrom($context);
	    }

	    public function nonLiteralKind() : ?NonLiteralKindContext
	    {
	    	return $this->getTypedRuleContext(NonLiteralKindContext::class, 0);
	    }

	    /**
	     * @return array<StringFacetContext>|StringFacetContext|null
	     */
	    public function stringFacet(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTypedRuleContexts(StringFacetContext::class);
	    	}

	        return $this->getTypedRuleContext(StringFacetContext::class, $index);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterLitNodeConstraintLiteral($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitLitNodeConstraintLiteral($this);
		    }
		}
	} 

	class NonLitNodeConstraintContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_nonLitNodeConstraint;
	    }

	    public function inlineNonLitNodeConstraint() : ?InlineNonLitNodeConstraintContext
	    {
	    	return $this->getTypedRuleContext(InlineNonLitNodeConstraintContext::class, 0);
	    }

	    /**
	     * @return array<AnnotationContext>|AnnotationContext|null
	     */
	    public function annotation(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTypedRuleContexts(AnnotationContext::class);
	    	}

	        return $this->getTypedRuleContext(AnnotationContext::class, $index);
	    }

	    /**
	     * @return array<SemanticActionContext>|SemanticActionContext|null
	     */
	    public function semanticAction(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTypedRuleContexts(SemanticActionContext::class);
	    	}

	        return $this->getTypedRuleContext(SemanticActionContext::class, $index);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterNonLitNodeConstraint($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitNonLitNodeConstraint($this);
		    }
		}
	} 

	class NonLiteralKindContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_nonLiteralKind;
	    }

	    public function KW_IRI() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::KW_IRI, 0);
	    }

	    public function KW_BNODE() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::KW_BNODE, 0);
	    }

	    public function KW_NONLITERAL() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::KW_NONLITERAL, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterNonLiteralKind($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitNonLiteralKind($this);
		    }
		}
	} 

	class XsFacetContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_xsFacet;
	    }

	    public function stringFacet() : ?StringFacetContext
	    {
	    	return $this->getTypedRuleContext(StringFacetContext::class, 0);
	    }

	    public function numericFacet() : ?NumericFacetContext
	    {
	    	return $this->getTypedRuleContext(NumericFacetContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterXsFacet($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitXsFacet($this);
		    }
		}
	} 

	class StringFacetContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_stringFacet;
	    }

	    public function stringLength() : ?StringLengthContext
	    {
	    	return $this->getTypedRuleContext(StringLengthContext::class, 0);
	    }

	    public function INTEGER() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::INTEGER, 0);
	    }

	    public function REGEXP() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::REGEXP, 0);
	    }

	    public function REGEXP_FLAGS() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::REGEXP_FLAGS, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterStringFacet($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitStringFacet($this);
		    }
		}
	} 

	class StringLengthContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_stringLength;
	    }

	    public function KW_LENGTH() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::KW_LENGTH, 0);
	    }

	    public function KW_MINLENGTH() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::KW_MINLENGTH, 0);
	    }

	    public function KW_MAXLENGTH() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::KW_MAXLENGTH, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterStringLength($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitStringLength($this);
		    }
		}
	} 

	class NumericFacetContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_numericFacet;
	    }

	    public function numericRange() : ?NumericRangeContext
	    {
	    	return $this->getTypedRuleContext(NumericRangeContext::class, 0);
	    }

	    public function rawNumeric() : ?RawNumericContext
	    {
	    	return $this->getTypedRuleContext(RawNumericContext::class, 0);
	    }

	    public function numericLength() : ?NumericLengthContext
	    {
	    	return $this->getTypedRuleContext(NumericLengthContext::class, 0);
	    }

	    public function INTEGER() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::INTEGER, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterNumericFacet($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitNumericFacet($this);
		    }
		}
	} 

	class NumericRangeContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_numericRange;
	    }

	    public function KW_MININCLUSIVE() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::KW_MININCLUSIVE, 0);
	    }

	    public function KW_MINEXCLUSIVE() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::KW_MINEXCLUSIVE, 0);
	    }

	    public function KW_MAXINCLUSIVE() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::KW_MAXINCLUSIVE, 0);
	    }

	    public function KW_MAXEXCLUSIVE() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::KW_MAXEXCLUSIVE, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterNumericRange($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitNumericRange($this);
		    }
		}
	} 

	class NumericLengthContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_numericLength;
	    }

	    public function KW_TOTALDIGITS() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::KW_TOTALDIGITS, 0);
	    }

	    public function KW_FRACTIONDIGITS() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::KW_FRACTIONDIGITS, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterNumericLength($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitNumericLength($this);
		    }
		}
	} 

	class RawNumericContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_rawNumeric;
	    }

	    public function INTEGER() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::INTEGER, 0);
	    }

	    public function DECIMAL() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::DECIMAL, 0);
	    }

	    public function DOUBLE() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::DOUBLE, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterRawNumeric($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitRawNumeric($this);
		    }
		}
	} 

	class ShapeDefinitionContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_shapeDefinition;
	    }

	    public function inlineShapeDefinition() : ?InlineShapeDefinitionContext
	    {
	    	return $this->getTypedRuleContext(InlineShapeDefinitionContext::class, 0);
	    }

	    /**
	     * @return array<AnnotationContext>|AnnotationContext|null
	     */
	    public function annotation(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTypedRuleContexts(AnnotationContext::class);
	    	}

	        return $this->getTypedRuleContext(AnnotationContext::class, $index);
	    }

	    /**
	     * @return array<SemanticActionContext>|SemanticActionContext|null
	     */
	    public function semanticAction(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTypedRuleContexts(SemanticActionContext::class);
	    	}

	        return $this->getTypedRuleContext(SemanticActionContext::class, $index);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterShapeDefinition($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitShapeDefinition($this);
		    }
		}
	} 

	class InlineShapeDefinitionContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_inlineShapeDefinition;
	    }

	    /**
	     * @return array<QualifierContext>|QualifierContext|null
	     */
	    public function qualifier(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTypedRuleContexts(QualifierContext::class);
	    	}

	        return $this->getTypedRuleContext(QualifierContext::class, $index);
	    }

	    public function tripleExpression() : ?TripleExpressionContext
	    {
	    	return $this->getTypedRuleContext(TripleExpressionContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterInlineShapeDefinition($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitInlineShapeDefinition($this);
		    }
		}
	} 

	class QualifierContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_qualifier;
	    }

	    public function extension() : ?ExtensionContext
	    {
	    	return $this->getTypedRuleContext(ExtensionContext::class, 0);
	    }

	    public function extraPropertySet() : ?ExtraPropertySetContext
	    {
	    	return $this->getTypedRuleContext(ExtraPropertySetContext::class, 0);
	    }

	    public function KW_CLOSED() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::KW_CLOSED, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterQualifier($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitQualifier($this);
		    }
		}
	} 

	class ExtraPropertySetContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_extraPropertySet;
	    }

	    public function KW_EXTRA() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::KW_EXTRA, 0);
	    }

	    /**
	     * @return array<PredicateContext>|PredicateContext|null
	     */
	    public function predicate(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTypedRuleContexts(PredicateContext::class);
	    	}

	        return $this->getTypedRuleContext(PredicateContext::class, $index);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterExtraPropertySet($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitExtraPropertySet($this);
		    }
		}
	} 

	class TripleExpressionContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_tripleExpression;
	    }

	    public function oneOfTripleExpr() : ?OneOfTripleExprContext
	    {
	    	return $this->getTypedRuleContext(OneOfTripleExprContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterTripleExpression($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitTripleExpression($this);
		    }
		}
	} 

	class OneOfTripleExprContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_oneOfTripleExpr;
	    }

	    public function groupTripleExpr() : ?GroupTripleExprContext
	    {
	    	return $this->getTypedRuleContext(GroupTripleExprContext::class, 0);
	    }

	    public function multiElementOneOf() : ?MultiElementOneOfContext
	    {
	    	return $this->getTypedRuleContext(MultiElementOneOfContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterOneOfTripleExpr($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitOneOfTripleExpr($this);
		    }
		}
	} 

	class MultiElementOneOfContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_multiElementOneOf;
	    }

	    /**
	     * @return array<GroupTripleExprContext>|GroupTripleExprContext|null
	     */
	    public function groupTripleExpr(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTypedRuleContexts(GroupTripleExprContext::class);
	    	}

	        return $this->getTypedRuleContext(GroupTripleExprContext::class, $index);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterMultiElementOneOf($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitMultiElementOneOf($this);
		    }
		}
	} 

	class GroupTripleExprContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_groupTripleExpr;
	    }

	    public function singleElementGroup() : ?SingleElementGroupContext
	    {
	    	return $this->getTypedRuleContext(SingleElementGroupContext::class, 0);
	    }

	    public function multiElementGroup() : ?MultiElementGroupContext
	    {
	    	return $this->getTypedRuleContext(MultiElementGroupContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterGroupTripleExpr($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitGroupTripleExpr($this);
		    }
		}
	} 

	class SingleElementGroupContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_singleElementGroup;
	    }

	    public function unaryTripleExpr() : ?UnaryTripleExprContext
	    {
	    	return $this->getTypedRuleContext(UnaryTripleExprContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterSingleElementGroup($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitSingleElementGroup($this);
		    }
		}
	} 

	class MultiElementGroupContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_multiElementGroup;
	    }

	    /**
	     * @return array<UnaryTripleExprContext>|UnaryTripleExprContext|null
	     */
	    public function unaryTripleExpr(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTypedRuleContexts(UnaryTripleExprContext::class);
	    	}

	        return $this->getTypedRuleContext(UnaryTripleExprContext::class, $index);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterMultiElementGroup($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitMultiElementGroup($this);
		    }
		}
	} 

	class UnaryTripleExprContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_unaryTripleExpr;
	    }

	    public function tripleConstraint() : ?TripleConstraintContext
	    {
	    	return $this->getTypedRuleContext(TripleConstraintContext::class, 0);
	    }

	    public function bracketedTripleExpr() : ?BracketedTripleExprContext
	    {
	    	return $this->getTypedRuleContext(BracketedTripleExprContext::class, 0);
	    }

	    public function tripleExprLabel() : ?TripleExprLabelContext
	    {
	    	return $this->getTypedRuleContext(TripleExprLabelContext::class, 0);
	    }

	    public function r_include() : ?R_includeContext
	    {
	    	return $this->getTypedRuleContext(R_includeContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterUnaryTripleExpr($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitUnaryTripleExpr($this);
		    }
		}
	} 

	class BracketedTripleExprContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_bracketedTripleExpr;
	    }

	    public function tripleExpression() : ?TripleExpressionContext
	    {
	    	return $this->getTypedRuleContext(TripleExpressionContext::class, 0);
	    }

	    public function cardinality() : ?CardinalityContext
	    {
	    	return $this->getTypedRuleContext(CardinalityContext::class, 0);
	    }

	    /**
	     * @return array<AnnotationContext>|AnnotationContext|null
	     */
	    public function annotation(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTypedRuleContexts(AnnotationContext::class);
	    	}

	        return $this->getTypedRuleContext(AnnotationContext::class, $index);
	    }

	    /**
	     * @return array<SemanticActionContext>|SemanticActionContext|null
	     */
	    public function semanticAction(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTypedRuleContexts(SemanticActionContext::class);
	    	}

	        return $this->getTypedRuleContext(SemanticActionContext::class, $index);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterBracketedTripleExpr($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitBracketedTripleExpr($this);
		    }
		}
	} 

	class TripleConstraintContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_tripleConstraint;
	    }

	    public function predicate() : ?PredicateContext
	    {
	    	return $this->getTypedRuleContext(PredicateContext::class, 0);
	    }

	    public function inlineShapeExpression() : ?InlineShapeExpressionContext
	    {
	    	return $this->getTypedRuleContext(InlineShapeExpressionContext::class, 0);
	    }

	    public function senseFlags() : ?SenseFlagsContext
	    {
	    	return $this->getTypedRuleContext(SenseFlagsContext::class, 0);
	    }

	    public function cardinality() : ?CardinalityContext
	    {
	    	return $this->getTypedRuleContext(CardinalityContext::class, 0);
	    }

	    /**
	     * @return array<AnnotationContext>|AnnotationContext|null
	     */
	    public function annotation(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTypedRuleContexts(AnnotationContext::class);
	    	}

	        return $this->getTypedRuleContext(AnnotationContext::class, $index);
	    }

	    /**
	     * @return array<SemanticActionContext>|SemanticActionContext|null
	     */
	    public function semanticAction(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTypedRuleContexts(SemanticActionContext::class);
	    	}

	        return $this->getTypedRuleContext(SemanticActionContext::class, $index);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterTripleConstraint($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitTripleConstraint($this);
		    }
		}
	} 

	class CardinalityContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_cardinality;
	    }
	 
		public function copyFrom(ParserRuleContext $context) : void
		{
			parent::copyFrom($context);

		}
	}

	class StarCardinalityContext extends CardinalityContext
	{
		public function __construct(CardinalityContext $context)
		{
		    parent::__construct($context);

		    $this->copyFrom($context);
	    }

	    public function UNBOUNDED() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::UNBOUNDED, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterStarCardinality($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitStarCardinality($this);
		    }
		}
	}

	class RepeatCardinalityContext extends CardinalityContext
	{
		public function __construct(CardinalityContext $context)
		{
		    parent::__construct($context);

		    $this->copyFrom($context);
	    }

	    public function repeatRange() : ?RepeatRangeContext
	    {
	    	return $this->getTypedRuleContext(RepeatRangeContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterRepeatCardinality($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitRepeatCardinality($this);
		    }
		}
	}

	class PlusCardinalityContext extends CardinalityContext
	{
		public function __construct(CardinalityContext $context)
		{
		    parent::__construct($context);

		    $this->copyFrom($context);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterPlusCardinality($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitPlusCardinality($this);
		    }
		}
	}

	class OptionalCardinalityContext extends CardinalityContext
	{
		public function __construct(CardinalityContext $context)
		{
		    parent::__construct($context);

		    $this->copyFrom($context);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterOptionalCardinality($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitOptionalCardinality($this);
		    }
		}
	} 

	class RepeatRangeContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_repeatRange;
	    }
	 
		public function copyFrom(ParserRuleContext $context) : void
		{
			parent::copyFrom($context);

		}
	}

	class ExactRangeContext extends RepeatRangeContext
	{
		public function __construct(RepeatRangeContext $context)
		{
		    parent::__construct($context);

		    $this->copyFrom($context);
	    }

	    public function INTEGER() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::INTEGER, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterExactRange($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitExactRange($this);
		    }
		}
	}

	class MinMaxRangeContext extends RepeatRangeContext
	{
		public function __construct(RepeatRangeContext $context)
		{
		    parent::__construct($context);

		    $this->copyFrom($context);
	    }

	    /**
	     * @return array<TerminalNode>|TerminalNode|null
	     */
	    public function INTEGER(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTokens(ShExDocParser::INTEGER);
	    	}

	        return $this->getToken(ShExDocParser::INTEGER, $index);
	    }

	    public function UNBOUNDED() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::UNBOUNDED, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterMinMaxRange($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitMinMaxRange($this);
		    }
		}
	} 

	class SenseFlagsContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_senseFlags;
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterSenseFlags($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitSenseFlags($this);
		    }
		}
	} 

	class ValueSetContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_valueSet;
	    }

	    /**
	     * @return array<ValueSetValueContext>|ValueSetValueContext|null
	     */
	    public function valueSetValue(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTypedRuleContexts(ValueSetValueContext::class);
	    	}

	        return $this->getTypedRuleContext(ValueSetValueContext::class, $index);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterValueSet($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitValueSet($this);
		    }
		}
	} 

	class ValueSetValueContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_valueSetValue;
	    }

	    public function iriRange() : ?IriRangeContext
	    {
	    	return $this->getTypedRuleContext(IriRangeContext::class, 0);
	    }

	    public function literalRange() : ?LiteralRangeContext
	    {
	    	return $this->getTypedRuleContext(LiteralRangeContext::class, 0);
	    }

	    public function languageRange() : ?LanguageRangeContext
	    {
	    	return $this->getTypedRuleContext(LanguageRangeContext::class, 0);
	    }

	    /**
	     * @return array<IriExclusionContext>|IriExclusionContext|null
	     */
	    public function iriExclusion(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTypedRuleContexts(IriExclusionContext::class);
	    	}

	        return $this->getTypedRuleContext(IriExclusionContext::class, $index);
	    }

	    /**
	     * @return array<LiteralExclusionContext>|LiteralExclusionContext|null
	     */
	    public function literalExclusion(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTypedRuleContexts(LiteralExclusionContext::class);
	    	}

	        return $this->getTypedRuleContext(LiteralExclusionContext::class, $index);
	    }

	    /**
	     * @return array<LanguageExclusionContext>|LanguageExclusionContext|null
	     */
	    public function languageExclusion(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTypedRuleContexts(LanguageExclusionContext::class);
	    	}

	        return $this->getTypedRuleContext(LanguageExclusionContext::class, $index);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterValueSetValue($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitValueSetValue($this);
		    }
		}
	} 

	class IriRangeContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_iriRange;
	    }

	    public function iri() : ?IriContext
	    {
	    	return $this->getTypedRuleContext(IriContext::class, 0);
	    }

	    public function STEM_MARK() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::STEM_MARK, 0);
	    }

	    /**
	     * @return array<IriExclusionContext>|IriExclusionContext|null
	     */
	    public function iriExclusion(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTypedRuleContexts(IriExclusionContext::class);
	    	}

	        return $this->getTypedRuleContext(IriExclusionContext::class, $index);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterIriRange($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitIriRange($this);
		    }
		}
	} 

	class IriExclusionContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_iriExclusion;
	    }

	    public function iri() : ?IriContext
	    {
	    	return $this->getTypedRuleContext(IriContext::class, 0);
	    }

	    public function STEM_MARK() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::STEM_MARK, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterIriExclusion($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitIriExclusion($this);
		    }
		}
	} 

	class LiteralRangeContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_literalRange;
	    }

	    public function literal() : ?LiteralContext
	    {
	    	return $this->getTypedRuleContext(LiteralContext::class, 0);
	    }

	    public function STEM_MARK() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::STEM_MARK, 0);
	    }

	    /**
	     * @return array<LiteralExclusionContext>|LiteralExclusionContext|null
	     */
	    public function literalExclusion(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTypedRuleContexts(LiteralExclusionContext::class);
	    	}

	        return $this->getTypedRuleContext(LiteralExclusionContext::class, $index);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterLiteralRange($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitLiteralRange($this);
		    }
		}
	} 

	class LiteralExclusionContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_literalExclusion;
	    }

	    public function literal() : ?LiteralContext
	    {
	    	return $this->getTypedRuleContext(LiteralContext::class, 0);
	    }

	    public function STEM_MARK() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::STEM_MARK, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterLiteralExclusion($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitLiteralExclusion($this);
		    }
		}
	} 

	class LanguageRangeContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_languageRange;
	    }
	 
		public function copyFrom(ParserRuleContext $context) : void
		{
			parent::copyFrom($context);

		}
	}

	class LanguageRangeFullContext extends LanguageRangeContext
	{
		public function __construct(LanguageRangeContext $context)
		{
		    parent::__construct($context);

		    $this->copyFrom($context);
	    }

	    public function LANGTAG() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::LANGTAG, 0);
	    }

	    public function STEM_MARK() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::STEM_MARK, 0);
	    }

	    /**
	     * @return array<LanguageExclusionContext>|LanguageExclusionContext|null
	     */
	    public function languageExclusion(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTypedRuleContexts(LanguageExclusionContext::class);
	    	}

	        return $this->getTypedRuleContext(LanguageExclusionContext::class, $index);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterLanguageRangeFull($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitLanguageRangeFull($this);
		    }
		}
	}

	class LanguageRangeAtContext extends LanguageRangeContext
	{
		public function __construct(LanguageRangeContext $context)
		{
		    parent::__construct($context);

		    $this->copyFrom($context);
	    }

	    public function STEM_MARK() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::STEM_MARK, 0);
	    }

	    /**
	     * @return array<LanguageExclusionContext>|LanguageExclusionContext|null
	     */
	    public function languageExclusion(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTypedRuleContexts(LanguageExclusionContext::class);
	    	}

	        return $this->getTypedRuleContext(LanguageExclusionContext::class, $index);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterLanguageRangeAt($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitLanguageRangeAt($this);
		    }
		}
	} 

	class LanguageExclusionContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_languageExclusion;
	    }

	    public function LANGTAG() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::LANGTAG, 0);
	    }

	    public function STEM_MARK() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::STEM_MARK, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterLanguageExclusion($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitLanguageExclusion($this);
		    }
		}
	} 

	class R_includeContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_r_include;
	    }

	    public function tripleExprLabel() : ?TripleExprLabelContext
	    {
	    	return $this->getTypedRuleContext(TripleExprLabelContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterR_include($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitR_include($this);
		    }
		}
	} 

	class AnnotationContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_annotation;
	    }

	    public function predicate() : ?PredicateContext
	    {
	    	return $this->getTypedRuleContext(PredicateContext::class, 0);
	    }

	    public function iri() : ?IriContext
	    {
	    	return $this->getTypedRuleContext(IriContext::class, 0);
	    }

	    public function literal() : ?LiteralContext
	    {
	    	return $this->getTypedRuleContext(LiteralContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterAnnotation($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitAnnotation($this);
		    }
		}
	} 

	class SemanticActionContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_semanticAction;
	    }

	    public function iri() : ?IriContext
	    {
	    	return $this->getTypedRuleContext(IriContext::class, 0);
	    }

	    public function CODE() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::CODE, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterSemanticAction($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitSemanticAction($this);
		    }
		}
	} 

	class LiteralContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_literal;
	    }

	    public function rdfLiteral() : ?RdfLiteralContext
	    {
	    	return $this->getTypedRuleContext(RdfLiteralContext::class, 0);
	    }

	    public function numericLiteral() : ?NumericLiteralContext
	    {
	    	return $this->getTypedRuleContext(NumericLiteralContext::class, 0);
	    }

	    public function booleanLiteral() : ?BooleanLiteralContext
	    {
	    	return $this->getTypedRuleContext(BooleanLiteralContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterLiteral($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitLiteral($this);
		    }
		}
	} 

	class PredicateContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_predicate;
	    }

	    public function iri() : ?IriContext
	    {
	    	return $this->getTypedRuleContext(IriContext::class, 0);
	    }

	    public function rdfType() : ?RdfTypeContext
	    {
	    	return $this->getTypedRuleContext(RdfTypeContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterPredicate($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitPredicate($this);
		    }
		}
	} 

	class RdfTypeContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_rdfType;
	    }

	    public function RDF_TYPE() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::RDF_TYPE, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterRdfType($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitRdfType($this);
		    }
		}
	} 

	class DatatypeContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_datatype;
	    }

	    public function iri() : ?IriContext
	    {
	    	return $this->getTypedRuleContext(IriContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterDatatype($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitDatatype($this);
		    }
		}
	} 

	class ShapeExprLabelContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_shapeExprLabel;
	    }

	    public function iri() : ?IriContext
	    {
	    	return $this->getTypedRuleContext(IriContext::class, 0);
	    }

	    public function blankNode() : ?BlankNodeContext
	    {
	    	return $this->getTypedRuleContext(BlankNodeContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterShapeExprLabel($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitShapeExprLabel($this);
		    }
		}
	} 

	class TripleExprLabelContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_tripleExprLabel;
	    }

	    public function iri() : ?IriContext
	    {
	    	return $this->getTypedRuleContext(IriContext::class, 0);
	    }

	    public function blankNode() : ?BlankNodeContext
	    {
	    	return $this->getTypedRuleContext(BlankNodeContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterTripleExprLabel($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitTripleExprLabel($this);
		    }
		}
	} 

	class NumericLiteralContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_numericLiteral;
	    }

	    public function INTEGER() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::INTEGER, 0);
	    }

	    public function DECIMAL() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::DECIMAL, 0);
	    }

	    public function DOUBLE() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::DOUBLE, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterNumericLiteral($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitNumericLiteral($this);
		    }
		}
	} 

	class RdfLiteralContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_rdfLiteral;
	    }

	    public function string() : ?StringContext
	    {
	    	return $this->getTypedRuleContext(StringContext::class, 0);
	    }

	    public function LANGTAG() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::LANGTAG, 0);
	    }

	    public function datatype() : ?DatatypeContext
	    {
	    	return $this->getTypedRuleContext(DatatypeContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterRdfLiteral($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitRdfLiteral($this);
		    }
		}
	} 

	class BooleanLiteralContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_booleanLiteral;
	    }

	    public function KW_TRUE() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::KW_TRUE, 0);
	    }

	    public function KW_FALSE() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::KW_FALSE, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterBooleanLiteral($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitBooleanLiteral($this);
		    }
		}
	} 

	class StringContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_string;
	    }

	    public function STRING_LITERAL_LONG1() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::STRING_LITERAL_LONG1, 0);
	    }

	    public function STRING_LITERAL_LONG2() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::STRING_LITERAL_LONG2, 0);
	    }

	    public function STRING_LITERAL1() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::STRING_LITERAL1, 0);
	    }

	    public function STRING_LITERAL2() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::STRING_LITERAL2, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterString($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitString($this);
		    }
		}
	} 

	class IriContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_iri;
	    }

	    public function IRIREF() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::IRIREF, 0);
	    }

	    public function prefixedName() : ?PrefixedNameContext
	    {
	    	return $this->getTypedRuleContext(PrefixedNameContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterIri($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitIri($this);
		    }
		}
	} 

	class PrefixedNameContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_prefixedName;
	    }

	    public function PNAME_LN() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::PNAME_LN, 0);
	    }

	    public function PNAME_NS() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::PNAME_NS, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterPrefixedName($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitPrefixedName($this);
		    }
		}
	} 

	class BlankNodeContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_blankNode;
	    }

	    public function BLANK_NODE_LABEL() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::BLANK_NODE_LABEL, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterBlankNode($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitBlankNode($this);
		    }
		}
	} 

	class ExtensionContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_extension;
	    }

	    public function KW_EXTENDS() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::KW_EXTENDS, 0);
	    }

	    public function shapeExprLabel() : ?ShapeExprLabelContext
	    {
	    	return $this->getTypedRuleContext(ShapeExprLabelContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterExtension($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitExtension($this);
		    }
		}
	} 

	class RestrictionsContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex() : int
		{
		    return ShExDocParser::RULE_restrictions;
	    }

	    public function KW_RESTRICTS() : ?TerminalNode
	    {
	        return $this->getToken(ShExDocParser::KW_RESTRICTS, 0);
	    }

	    public function shapeExprLabel() : ?ShapeExprLabelContext
	    {
	    	return $this->getTypedRuleContext(ShapeExprLabelContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->enterRestrictions($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener) : void
		{
			if ($listener instanceof ShExDocListener) {
			    $listener->exitRestrictions($this);
		    }
		}
	} 
}