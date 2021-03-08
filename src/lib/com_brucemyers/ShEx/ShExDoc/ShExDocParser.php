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
		    [0x3,0x608B,0xA72A,0x8133,0xB9ED,0x417C,0x3BE7,0x7786,0x5964,
		    0x3,0x4A,0x2C6,0x4,0x2,0x9,0x2,0x4,0x3,0x9,0x3,0x4,0x4,
		    0x9,0x4,0x4,0x5,0x9,0x5,0x4,0x6,0x9,0x6,0x4,0x7,0x9,
		    0x7,0x4,0x8,0x9,0x8,0x4,0x9,0x9,0x9,0x4,0xA,0x9,0xA,
		    0x4,0xB,0x9,0xB,0x4,0xC,0x9,0xC,0x4,0xD,0x9,0xD,0x4,
		    0xE,0x9,0xE,0x4,0xF,0x9,0xF,0x4,0x10,0x9,0x10,0x4,0x11,
		    0x9,0x11,0x4,0x12,0x9,0x12,0x4,0x13,0x9,0x13,0x4,0x14,
		    0x9,0x14,0x4,0x15,0x9,0x15,0x4,0x16,0x9,0x16,0x4,0x17,
		    0x9,0x17,0x4,0x18,0x9,0x18,0x4,0x19,0x9,0x19,0x4,0x1A,
		    0x9,0x1A,0x4,0x1B,0x9,0x1B,0x4,0x1C,0x9,0x1C,0x4,0x1D,
		    0x9,0x1D,0x4,0x1E,0x9,0x1E,0x4,0x1F,0x9,0x1F,0x4,0x20,
		    0x9,0x20,0x4,0x21,0x9,0x21,0x4,0x22,0x9,0x22,0x4,0x23,
		    0x9,0x23,0x4,0x24,0x9,0x24,0x4,0x25,0x9,0x25,0x4,0x26,
		    0x9,0x26,0x4,0x27,0x9,0x27,0x4,0x28,0x9,0x28,0x4,0x29,
		    0x9,0x29,0x4,0x2A,0x9,0x2A,0x4,0x2B,0x9,0x2B,0x4,0x2C,
		    0x9,0x2C,0x4,0x2D,0x9,0x2D,0x4,0x2E,0x9,0x2E,0x4,0x2F,
		    0x9,0x2F,0x4,0x30,0x9,0x30,0x4,0x31,0x9,0x31,0x4,0x32,
		    0x9,0x32,0x4,0x33,0x9,0x33,0x4,0x34,0x9,0x34,0x4,0x35,
		    0x9,0x35,0x4,0x36,0x9,0x36,0x4,0x37,0x9,0x37,0x4,0x38,
		    0x9,0x38,0x4,0x39,0x9,0x39,0x4,0x3A,0x9,0x3A,0x4,0x3B,
		    0x9,0x3B,0x4,0x3C,0x9,0x3C,0x4,0x3D,0x9,0x3D,0x4,0x3E,
		    0x9,0x3E,0x4,0x3F,0x9,0x3F,0x4,0x40,0x9,0x40,0x4,0x41,
		    0x9,0x41,0x4,0x42,0x9,0x42,0x4,0x43,0x9,0x43,0x4,0x44,
		    0x9,0x44,0x4,0x45,0x9,0x45,0x4,0x46,0x9,0x46,0x4,0x47,
		    0x9,0x47,0x4,0x48,0x9,0x48,0x4,0x49,0x9,0x49,0x4,0x4A,
		    0x9,0x4A,0x4,0x4B,0x9,0x4B,0x4,0x4C,0x9,0x4C,0x4,0x4D,
		    0x9,0x4D,0x4,0x4E,0x9,0x4E,0x3,0x2,0x7,0x2,0x9E,0xA,0x2,
		    0xC,0x2,0xE,0x2,0xA1,0xB,0x2,0x3,0x2,0x3,0x2,0x5,0x2,
		    0xA5,0xA,0x2,0x3,0x2,0x7,0x2,0xA8,0xA,0x2,0xC,0x2,0xE,
		    0x2,0xAB,0xB,0x2,0x5,0x2,0xAD,0xA,0x2,0x3,0x2,0x3,0x2,
		    0x3,0x3,0x3,0x3,0x3,0x3,0x5,0x3,0xB4,0xA,0x3,0x3,0x4,
		    0x3,0x4,0x3,0x4,0x3,0x5,0x3,0x5,0x3,0x5,0x3,0x5,0x3,
		    0x6,0x3,0x6,0x3,0x6,0x3,0x7,0x3,0x7,0x5,0x7,0xC2,0xA,
		    0x7,0x3,0x8,0x3,0x8,0x3,0x8,0x3,0x8,0x3,0x9,0x6,0x9,
		    0xC9,0xA,0x9,0xD,0x9,0xE,0x9,0xCA,0x3,0xA,0x3,0xA,0x5,
		    0xA,0xCF,0xA,0xA,0x3,0xB,0x5,0xB,0xD2,0xA,0xB,0x3,0xB,
		    0x3,0xB,0x7,0xB,0xD6,0xA,0xB,0xC,0xB,0xE,0xB,0xD9,0xB,
		    0xB,0x3,0xB,0x3,0xB,0x5,0xB,0xDD,0xA,0xB,0x3,0xC,0x3,
		    0xC,0x3,0xD,0x3,0xD,0x3,0xE,0x3,0xE,0x3,0xE,0x7,0xE,
		    0xE6,0xA,0xE,0xC,0xE,0xE,0xE,0xE9,0xB,0xE,0x3,0xF,0x3,
		    0xF,0x3,0xF,0x7,0xF,0xEE,0xA,0xF,0xC,0xF,0xE,0xF,0xF1,
		    0xB,0xF,0x3,0x10,0x3,0x10,0x3,0x10,0x7,0x10,0xF6,0xA,
		    0x10,0xC,0x10,0xE,0x10,0xF9,0xB,0x10,0x3,0x11,0x3,0x11,
		    0x3,0x11,0x7,0x11,0xFE,0xA,0x11,0xC,0x11,0xE,0x11,0x101,
		    0xB,0x11,0x3,0x12,0x5,0x12,0x104,0xA,0x12,0x3,0x12,0x3,
		    0x12,0x3,0x13,0x5,0x13,0x109,0xA,0x13,0x3,0x13,0x3,0x13,
		    0x3,0x14,0x3,0x14,0x5,0x14,0x10F,0xA,0x14,0x3,0x14,0x3,
		    0x14,0x3,0x14,0x5,0x14,0x114,0xA,0x14,0x3,0x14,0x3,0x14,
		    0x3,0x14,0x3,0x14,0x3,0x14,0x5,0x14,0x11B,0xA,0x14,0x3,
		    0x15,0x3,0x15,0x5,0x15,0x11F,0xA,0x15,0x3,0x15,0x3,0x15,
		    0x3,0x15,0x5,0x15,0x124,0xA,0x15,0x3,0x15,0x3,0x15,0x3,
		    0x15,0x3,0x15,0x3,0x15,0x5,0x15,0x12B,0xA,0x15,0x3,0x16,
		    0x3,0x16,0x5,0x16,0x12F,0xA,0x16,0x3,0x17,0x3,0x17,0x5,
		    0x17,0x133,0xA,0x17,0x3,0x18,0x3,0x18,0x3,0x18,0x3,0x18,
		    0x5,0x18,0x139,0xA,0x18,0x3,0x19,0x3,0x19,0x7,0x19,0x13D,
		    0xA,0x19,0xC,0x19,0xE,0x19,0x140,0xB,0x19,0x3,0x19,0x3,
		    0x19,0x7,0x19,0x144,0xA,0x19,0xC,0x19,0xE,0x19,0x147,0xB,
		    0x19,0x3,0x19,0x3,0x19,0x7,0x19,0x14B,0xA,0x19,0xC,0x19,
		    0xE,0x19,0x14E,0xB,0x19,0x3,0x19,0x3,0x19,0x7,0x19,0x152,
		    0xA,0x19,0xC,0x19,0xE,0x19,0x155,0xB,0x19,0x3,0x19,0x6,
		    0x19,0x158,0xA,0x19,0xD,0x19,0xE,0x19,0x159,0x5,0x19,0x15C,
		    0xA,0x19,0x3,0x1A,0x3,0x1A,0x7,0x1A,0x160,0xA,0x1A,0xC,
		    0x1A,0xE,0x1A,0x163,0xB,0x1A,0x3,0x1A,0x7,0x1A,0x166,0xA,
		    0x1A,0xC,0x1A,0xE,0x1A,0x169,0xB,0x1A,0x3,0x1B,0x3,0x1B,
		    0x7,0x1B,0x16D,0xA,0x1B,0xC,0x1B,0xE,0x1B,0x170,0xB,0x1B,
		    0x3,0x1B,0x6,0x1B,0x173,0xA,0x1B,0xD,0x1B,0xE,0x1B,0x174,
		    0x5,0x1B,0x177,0xA,0x1B,0x3,0x1C,0x3,0x1C,0x7,0x1C,0x17B,
		    0xA,0x1C,0xC,0x1C,0xE,0x1C,0x17E,0xB,0x1C,0x3,0x1C,0x7,
		    0x1C,0x181,0xA,0x1C,0xC,0x1C,0xE,0x1C,0x184,0xB,0x1C,0x3,
		    0x1D,0x3,0x1D,0x3,0x1E,0x3,0x1E,0x5,0x1E,0x18A,0xA,0x1E,
		    0x3,0x1F,0x3,0x1F,0x3,0x1F,0x3,0x1F,0x3,0x1F,0x5,0x1F,
		    0x191,0xA,0x1F,0x5,0x1F,0x193,0xA,0x1F,0x3,0x20,0x3,0x20,
		    0x3,0x21,0x3,0x21,0x3,0x21,0x3,0x21,0x3,0x21,0x3,0x21,
		    0x5,0x21,0x19D,0xA,0x21,0x3,0x22,0x3,0x22,0x3,0x23,0x3,
		    0x23,0x3,0x24,0x3,0x24,0x3,0x25,0x3,0x25,0x7,0x25,0x1A7,
		    0xA,0x25,0xC,0x25,0xE,0x25,0x1AA,0xB,0x25,0x3,0x25,0x7,
		    0x25,0x1AD,0xA,0x25,0xC,0x25,0xE,0x25,0x1B0,0xB,0x25,0x3,
		    0x26,0x7,0x26,0x1B3,0xA,0x26,0xC,0x26,0xE,0x26,0x1B6,0xB,
		    0x26,0x3,0x26,0x3,0x26,0x5,0x26,0x1BA,0xA,0x26,0x3,0x26,
		    0x3,0x26,0x3,0x27,0x3,0x27,0x3,0x27,0x5,0x27,0x1C1,0xA,
		    0x27,0x3,0x28,0x3,0x28,0x6,0x28,0x1C5,0xA,0x28,0xD,0x28,
		    0xE,0x28,0x1C6,0x3,0x29,0x3,0x29,0x3,0x2A,0x3,0x2A,0x5,
		    0x2A,0x1CD,0xA,0x2A,0x3,0x2B,0x3,0x2B,0x3,0x2B,0x6,0x2B,
		    0x1D2,0xA,0x2B,0xD,0x2B,0xE,0x2B,0x1D3,0x3,0x2C,0x3,0x2C,
		    0x5,0x2C,0x1D8,0xA,0x2C,0x3,0x2D,0x3,0x2D,0x5,0x2D,0x1DC,
		    0xA,0x2D,0x3,0x2E,0x3,0x2E,0x3,0x2E,0x6,0x2E,0x1E1,0xA,
		    0x2E,0xD,0x2E,0xE,0x2E,0x1E2,0x3,0x2E,0x5,0x2E,0x1E6,0xA,
		    0x2E,0x3,0x2F,0x3,0x2F,0x5,0x2F,0x1EA,0xA,0x2F,0x3,0x2F,
		    0x3,0x2F,0x5,0x2F,0x1EE,0xA,0x2F,0x3,0x2F,0x5,0x2F,0x1F1,
		    0xA,0x2F,0x3,0x30,0x3,0x30,0x3,0x30,0x3,0x30,0x5,0x30,
		    0x1F7,0xA,0x30,0x3,0x30,0x7,0x30,0x1FA,0xA,0x30,0xC,0x30,
		    0xE,0x30,0x1FD,0xB,0x30,0x3,0x30,0x7,0x30,0x200,0xA,0x30,
		    0xC,0x30,0xE,0x30,0x203,0xB,0x30,0x3,0x31,0x5,0x31,0x206,
		    0xA,0x31,0x3,0x31,0x3,0x31,0x3,0x31,0x5,0x31,0x20B,0xA,
		    0x31,0x3,0x31,0x7,0x31,0x20E,0xA,0x31,0xC,0x31,0xE,0x31,
		    0x211,0xB,0x31,0x3,0x31,0x7,0x31,0x214,0xA,0x31,0xC,0x31,
		    0xE,0x31,0x217,0xB,0x31,0x3,0x32,0x3,0x32,0x3,0x32,0x3,
		    0x32,0x5,0x32,0x21D,0xA,0x32,0x3,0x33,0x3,0x33,0x3,0x33,
		    0x3,0x33,0x3,0x33,0x3,0x33,0x3,0x33,0x5,0x33,0x226,0xA,
		    0x33,0x3,0x33,0x5,0x33,0x229,0xA,0x33,0x3,0x34,0x3,0x34,
		    0x3,0x35,0x3,0x35,0x7,0x35,0x22F,0xA,0x35,0xC,0x35,0xE,
		    0x35,0x232,0xB,0x35,0x3,0x35,0x3,0x35,0x3,0x36,0x3,0x36,
		    0x3,0x36,0x3,0x36,0x3,0x36,0x6,0x36,0x23B,0xA,0x36,0xD,
		    0x36,0xE,0x36,0x23C,0x3,0x36,0x6,0x36,0x240,0xA,0x36,0xD,
		    0x36,0xE,0x36,0x241,0x3,0x36,0x6,0x36,0x245,0xA,0x36,0xD,
		    0x36,0xE,0x36,0x246,0x5,0x36,0x249,0xA,0x36,0x5,0x36,0x24B,
		    0xA,0x36,0x3,0x37,0x3,0x37,0x3,0x37,0x7,0x37,0x250,0xA,
		    0x37,0xC,0x37,0xE,0x37,0x253,0xB,0x37,0x5,0x37,0x255,0xA,
		    0x37,0x3,0x38,0x3,0x38,0x3,0x38,0x5,0x38,0x25A,0xA,0x38,
		    0x3,0x39,0x3,0x39,0x3,0x39,0x7,0x39,0x25F,0xA,0x39,0xC,
		    0x39,0xE,0x39,0x262,0xB,0x39,0x5,0x39,0x264,0xA,0x39,0x3,
		    0x3A,0x3,0x3A,0x3,0x3A,0x5,0x3A,0x269,0xA,0x3A,0x3,0x3B,
		    0x3,0x3B,0x3,0x3B,0x7,0x3B,0x26E,0xA,0x3B,0xC,0x3B,0xE,
		    0x3B,0x271,0xB,0x3B,0x5,0x3B,0x273,0xA,0x3B,0x3,0x3B,0x3,
		    0x3B,0x3,0x3B,0x7,0x3B,0x278,0xA,0x3B,0xC,0x3B,0xE,0x3B,
		    0x27B,0xB,0x3B,0x5,0x3B,0x27D,0xA,0x3B,0x3,0x3C,0x3,0x3C,
		    0x3,0x3C,0x5,0x3C,0x282,0xA,0x3C,0x3,0x3D,0x3,0x3D,0x3,
		    0x3D,0x3,0x3E,0x3,0x3E,0x3,0x3E,0x3,0x3E,0x5,0x3E,0x28B,
		    0xA,0x3E,0x3,0x3F,0x3,0x3F,0x3,0x3F,0x3,0x3F,0x3,0x40,
		    0x3,0x40,0x3,0x40,0x5,0x40,0x294,0xA,0x40,0x3,0x41,0x3,
		    0x41,0x5,0x41,0x298,0xA,0x41,0x3,0x42,0x3,0x42,0x3,0x43,
		    0x3,0x43,0x3,0x44,0x3,0x44,0x5,0x44,0x2A0,0xA,0x44,0x3,
		    0x45,0x3,0x45,0x5,0x45,0x2A4,0xA,0x45,0x3,0x46,0x3,0x46,
		    0x3,0x47,0x3,0x47,0x3,0x47,0x3,0x47,0x5,0x47,0x2AC,0xA,
		    0x47,0x3,0x48,0x3,0x48,0x3,0x49,0x3,0x49,0x3,0x4A,0x3,
		    0x4A,0x5,0x4A,0x2B4,0xA,0x4A,0x3,0x4B,0x3,0x4B,0x3,0x4C,
		    0x3,0x4C,0x3,0x4D,0x3,0x4D,0x3,0x4D,0x3,0x4D,0x5,0x4D,
		    0x2BE,0xA,0x4D,0x3,0x4E,0x3,0x4E,0x3,0x4E,0x3,0x4E,0x5,
		    0x4E,0x2C4,0xA,0x4E,0x3,0x4E,0x2,0x2,0x4F,0x2,0x4,0x6,
		    0x8,0xA,0xC,0xE,0x10,0x12,0x14,0x16,0x18,0x1A,0x1C,0x1E,
		    0x20,0x22,0x24,0x26,0x28,0x2A,0x2C,0x2E,0x30,0x32,0x34,
		    0x36,0x38,0x3A,0x3C,0x3E,0x40,0x42,0x44,0x46,0x48,0x4A,
		    0x4C,0x4E,0x50,0x52,0x54,0x56,0x58,0x5A,0x5C,0x5E,0x60,
		    0x62,0x64,0x66,0x68,0x6A,0x6C,0x6E,0x70,0x72,0x74,0x76,
		    0x78,0x7A,0x7C,0x7E,0x80,0x82,0x84,0x86,0x88,0x8A,0x8C,
		    0x8E,0x90,0x92,0x94,0x96,0x98,0x9A,0x2,0xC,0x3,0x2,0x24,
		    0x26,0x3,0x2,0x2D,0x2F,0x3,0x2,0x29,0x2C,0x3,0x2,0x30,
		    0x31,0x3,0x2,0x42,0x44,0x4,0x2,0x42,0x42,0x46,0x46,0x4,
		    0x2,0x16,0x16,0x37,0x37,0x3,0x2,0x33,0x34,0x3,0x2,0x47,
		    0x4A,0x3,0x2,0x3A,0x3B,0x2,0x2E9,0x2,0x9F,0x3,0x2,0x2,
		    0x2,0x4,0xB3,0x3,0x2,0x2,0x2,0x6,0xB5,0x3,0x2,0x2,0x2,
		    0x8,0xB8,0x3,0x2,0x2,0x2,0xA,0xBC,0x3,0x2,0x2,0x2,0xC,
		    0xC1,0x3,0x2,0x2,0x2,0xE,0xC3,0x3,0x2,0x2,0x2,0x10,0xC8,
		    0x3,0x2,0x2,0x2,0x12,0xCE,0x3,0x2,0x2,0x2,0x14,0xD1,0x3,
		    0x2,0x2,0x2,0x16,0xDE,0x3,0x2,0x2,0x2,0x18,0xE0,0x3,0x2,
		    0x2,0x2,0x1A,0xE2,0x3,0x2,0x2,0x2,0x1C,0xEA,0x3,0x2,0x2,
		    0x2,0x1E,0xF2,0x3,0x2,0x2,0x2,0x20,0xFA,0x3,0x2,0x2,0x2,
		    0x22,0x103,0x3,0x2,0x2,0x2,0x24,0x108,0x3,0x2,0x2,0x2,
		    0x26,0x11A,0x3,0x2,0x2,0x2,0x28,0x12A,0x3,0x2,0x2,0x2,
		    0x2A,0x12E,0x3,0x2,0x2,0x2,0x2C,0x132,0x3,0x2,0x2,0x2,
		    0x2E,0x138,0x3,0x2,0x2,0x2,0x30,0x15B,0x3,0x2,0x2,0x2,
		    0x32,0x15D,0x3,0x2,0x2,0x2,0x34,0x176,0x3,0x2,0x2,0x2,
		    0x36,0x178,0x3,0x2,0x2,0x2,0x38,0x185,0x3,0x2,0x2,0x2,
		    0x3A,0x189,0x3,0x2,0x2,0x2,0x3C,0x192,0x3,0x2,0x2,0x2,
		    0x3E,0x194,0x3,0x2,0x2,0x2,0x40,0x19C,0x3,0x2,0x2,0x2,
		    0x42,0x19E,0x3,0x2,0x2,0x2,0x44,0x1A0,0x3,0x2,0x2,0x2,
		    0x46,0x1A2,0x3,0x2,0x2,0x2,0x48,0x1A4,0x3,0x2,0x2,0x2,
		    0x4A,0x1B4,0x3,0x2,0x2,0x2,0x4C,0x1C0,0x3,0x2,0x2,0x2,
		    0x4E,0x1C2,0x3,0x2,0x2,0x2,0x50,0x1C8,0x3,0x2,0x2,0x2,
		    0x52,0x1CC,0x3,0x2,0x2,0x2,0x54,0x1CE,0x3,0x2,0x2,0x2,
		    0x56,0x1D7,0x3,0x2,0x2,0x2,0x58,0x1D9,0x3,0x2,0x2,0x2,
		    0x5A,0x1DD,0x3,0x2,0x2,0x2,0x5C,0x1F0,0x3,0x2,0x2,0x2,
		    0x5E,0x1F2,0x3,0x2,0x2,0x2,0x60,0x205,0x3,0x2,0x2,0x2,
		    0x62,0x21C,0x3,0x2,0x2,0x2,0x64,0x228,0x3,0x2,0x2,0x2,
		    0x66,0x22A,0x3,0x2,0x2,0x2,0x68,0x22C,0x3,0x2,0x2,0x2,
		    0x6A,0x24A,0x3,0x2,0x2,0x2,0x6C,0x24C,0x3,0x2,0x2,0x2,
		    0x6E,0x256,0x3,0x2,0x2,0x2,0x70,0x25B,0x3,0x2,0x2,0x2,
		    0x72,0x265,0x3,0x2,0x2,0x2,0x74,0x27C,0x3,0x2,0x2,0x2,
		    0x76,0x27E,0x3,0x2,0x2,0x2,0x78,0x283,0x3,0x2,0x2,0x2,
		    0x7A,0x286,0x3,0x2,0x2,0x2,0x7C,0x28C,0x3,0x2,0x2,0x2,
		    0x7E,0x293,0x3,0x2,0x2,0x2,0x80,0x297,0x3,0x2,0x2,0x2,
		    0x82,0x299,0x3,0x2,0x2,0x2,0x84,0x29B,0x3,0x2,0x2,0x2,
		    0x86,0x29F,0x3,0x2,0x2,0x2,0x88,0x2A3,0x3,0x2,0x2,0x2,
		    0x8A,0x2A5,0x3,0x2,0x2,0x2,0x8C,0x2A7,0x3,0x2,0x2,0x2,
		    0x8E,0x2AD,0x3,0x2,0x2,0x2,0x90,0x2AF,0x3,0x2,0x2,0x2,
		    0x92,0x2B3,0x3,0x2,0x2,0x2,0x94,0x2B5,0x3,0x2,0x2,0x2,
		    0x96,0x2B7,0x3,0x2,0x2,0x2,0x98,0x2BD,0x3,0x2,0x2,0x2,
		    0x9A,0x2C3,0x3,0x2,0x2,0x2,0x9C,0x9E,0x5,0x4,0x3,0x2,
		    0x9D,0x9C,0x3,0x2,0x2,0x2,0x9E,0xA1,0x3,0x2,0x2,0x2,0x9F,
		    0x9D,0x3,0x2,0x2,0x2,0x9F,0xA0,0x3,0x2,0x2,0x2,0xA0,0xAC,
		    0x3,0x2,0x2,0x2,0xA1,0x9F,0x3,0x2,0x2,0x2,0xA2,0xA5,0x5,
		    0xC,0x7,0x2,0xA3,0xA5,0x5,0x10,0x9,0x2,0xA4,0xA2,0x3,
		    0x2,0x2,0x2,0xA4,0xA3,0x3,0x2,0x2,0x2,0xA5,0xA9,0x3,0x2,
		    0x2,0x2,0xA6,0xA8,0x5,0x12,0xA,0x2,0xA7,0xA6,0x3,0x2,
		    0x2,0x2,0xA8,0xAB,0x3,0x2,0x2,0x2,0xA9,0xA7,0x3,0x2,0x2,
		    0x2,0xA9,0xAA,0x3,0x2,0x2,0x2,0xAA,0xAD,0x3,0x2,0x2,0x2,
		    0xAB,0xA9,0x3,0x2,0x2,0x2,0xAC,0xA4,0x3,0x2,0x2,0x2,0xAC,
		    0xAD,0x3,0x2,0x2,0x2,0xAD,0xAE,0x3,0x2,0x2,0x2,0xAE,0xAF,
		    0x7,0x2,0x2,0x3,0xAF,0x3,0x3,0x2,0x2,0x2,0xB0,0xB4,0x5,
		    0x6,0x4,0x2,0xB1,0xB4,0x5,0x8,0x5,0x2,0xB2,0xB4,0x5,0xA,
		    0x6,0x2,0xB3,0xB0,0x3,0x2,0x2,0x2,0xB3,0xB1,0x3,0x2,0x2,
		    0x2,0xB3,0xB2,0x3,0x2,0x2,0x2,0xB4,0x5,0x3,0x2,0x2,0x2,
		    0xB5,0xB6,0x7,0x19,0x2,0x2,0xB6,0xB7,0x7,0x39,0x2,0x2,
		    0xB7,0x7,0x3,0x2,0x2,0x2,0xB8,0xB9,0x7,0x1E,0x2,0x2,0xB9,
		    0xBA,0x7,0x3A,0x2,0x2,0xBA,0xBB,0x7,0x39,0x2,0x2,0xBB,
		    0x9,0x3,0x2,0x2,0x2,0xBC,0xBD,0x7,0x1B,0x2,0x2,0xBD,0xBE,
		    0x7,0x39,0x2,0x2,0xBE,0xB,0x3,0x2,0x2,0x2,0xBF,0xC2,0x5,
		    0xE,0x8,0x2,0xC0,0xC2,0x5,0x14,0xB,0x2,0xC1,0xBF,0x3,
		    0x2,0x2,0x2,0xC1,0xC0,0x3,0x2,0x2,0x2,0xC2,0xD,0x3,0x2,
		    0x2,0x2,0xC3,0xC4,0x7,0x1F,0x2,0x2,0xC4,0xC5,0x7,0x3,
		    0x2,0x2,0xC5,0xC6,0x5,0x16,0xC,0x2,0xC6,0xF,0x3,0x2,0x2,
		    0x2,0xC7,0xC9,0x5,0x7C,0x3F,0x2,0xC8,0xC7,0x3,0x2,0x2,
		    0x2,0xC9,0xCA,0x3,0x2,0x2,0x2,0xCA,0xC8,0x3,0x2,0x2,0x2,
		    0xCA,0xCB,0x3,0x2,0x2,0x2,0xCB,0x11,0x3,0x2,0x2,0x2,0xCC,
		    0xCF,0x5,0x4,0x3,0x2,0xCD,0xCF,0x5,0xC,0x7,0x2,0xCE,0xCC,
		    0x3,0x2,0x2,0x2,0xCE,0xCD,0x3,0x2,0x2,0x2,0xCF,0x13,0x3,
		    0x2,0x2,0x2,0xD0,0xD2,0x7,0x18,0x2,0x2,0xD1,0xD0,0x3,
		    0x2,0x2,0x2,0xD1,0xD2,0x3,0x2,0x2,0x2,0xD2,0xD3,0x3,0x2,
		    0x2,0x2,0xD3,0xD7,0x5,0x86,0x44,0x2,0xD4,0xD6,0x5,0x9A,
		    0x4E,0x2,0xD5,0xD4,0x3,0x2,0x2,0x2,0xD6,0xD9,0x3,0x2,
		    0x2,0x2,0xD7,0xD5,0x3,0x2,0x2,0x2,0xD7,0xD8,0x3,0x2,0x2,
		    0x2,0xD8,0xDC,0x3,0x2,0x2,0x2,0xD9,0xD7,0x3,0x2,0x2,0x2,
		    0xDA,0xDD,0x5,0x16,0xC,0x2,0xDB,0xDD,0x7,0x1D,0x2,0x2,
		    0xDC,0xDA,0x3,0x2,0x2,0x2,0xDC,0xDB,0x3,0x2,0x2,0x2,0xDD,
		    0x15,0x3,0x2,0x2,0x2,0xDE,0xDF,0x5,0x1A,0xE,0x2,0xDF,
		    0x17,0x3,0x2,0x2,0x2,0xE0,0xE1,0x5,0x1C,0xF,0x2,0xE1,
		    0x19,0x3,0x2,0x2,0x2,0xE2,0xE7,0x5,0x1E,0x10,0x2,0xE3,
		    0xE4,0x7,0x28,0x2,0x2,0xE4,0xE6,0x5,0x1E,0x10,0x2,0xE5,
		    0xE3,0x3,0x2,0x2,0x2,0xE6,0xE9,0x3,0x2,0x2,0x2,0xE7,0xE5,
		    0x3,0x2,0x2,0x2,0xE7,0xE8,0x3,0x2,0x2,0x2,0xE8,0x1B,0x3,
		    0x2,0x2,0x2,0xE9,0xE7,0x3,0x2,0x2,0x2,0xEA,0xEF,0x5,0x20,
		    0x11,0x2,0xEB,0xEC,0x7,0x28,0x2,0x2,0xEC,0xEE,0x5,0x20,
		    0x11,0x2,0xED,0xEB,0x3,0x2,0x2,0x2,0xEE,0xF1,0x3,0x2,
		    0x2,0x2,0xEF,0xED,0x3,0x2,0x2,0x2,0xEF,0xF0,0x3,0x2,0x2,
		    0x2,0xF0,0x1D,0x3,0x2,0x2,0x2,0xF1,0xEF,0x3,0x2,0x2,0x2,
		    0xF2,0xF7,0x5,0x22,0x12,0x2,0xF3,0xF4,0x7,0x27,0x2,0x2,
		    0xF4,0xF6,0x5,0x22,0x12,0x2,0xF5,0xF3,0x3,0x2,0x2,0x2,
		    0xF6,0xF9,0x3,0x2,0x2,0x2,0xF7,0xF5,0x3,0x2,0x2,0x2,0xF7,
		    0xF8,0x3,0x2,0x2,0x2,0xF8,0x1F,0x3,0x2,0x2,0x2,0xF9,0xF7,
		    0x3,0x2,0x2,0x2,0xFA,0xFF,0x5,0x24,0x13,0x2,0xFB,0xFC,
		    0x7,0x27,0x2,0x2,0xFC,0xFE,0x5,0x24,0x13,0x2,0xFD,0xFB,
		    0x3,0x2,0x2,0x2,0xFE,0x101,0x3,0x2,0x2,0x2,0xFF,0xFD,
		    0x3,0x2,0x2,0x2,0xFF,0x100,0x3,0x2,0x2,0x2,0x100,0x21,
		    0x3,0x2,0x2,0x2,0x101,0xFF,0x3,0x2,0x2,0x2,0x102,0x104,
		    0x7,0x32,0x2,0x2,0x103,0x102,0x3,0x2,0x2,0x2,0x103,0x104,
		    0x3,0x2,0x2,0x2,0x104,0x105,0x3,0x2,0x2,0x2,0x105,0x106,
		    0x5,0x26,0x14,0x2,0x106,0x23,0x3,0x2,0x2,0x2,0x107,0x109,
		    0x7,0x32,0x2,0x2,0x108,0x107,0x3,0x2,0x2,0x2,0x108,0x109,
		    0x3,0x2,0x2,0x2,0x109,0x10A,0x3,0x2,0x2,0x2,0x10A,0x10B,
		    0x5,0x28,0x15,0x2,0x10B,0x25,0x3,0x2,0x2,0x2,0x10C,0x10E,
		    0x5,0x36,0x1C,0x2,0x10D,0x10F,0x5,0x2A,0x16,0x2,0x10E,
		    0x10D,0x3,0x2,0x2,0x2,0x10E,0x10F,0x3,0x2,0x2,0x2,0x10F,
		    0x11B,0x3,0x2,0x2,0x2,0x110,0x11B,0x5,0x32,0x1A,0x2,0x111,
		    0x113,0x5,0x2A,0x16,0x2,0x112,0x114,0x5,0x36,0x1C,0x2,
		    0x113,0x112,0x3,0x2,0x2,0x2,0x113,0x114,0x3,0x2,0x2,0x2,
		    0x114,0x11B,0x3,0x2,0x2,0x2,0x115,0x116,0x7,0x4,0x2,0x2,
		    0x116,0x117,0x5,0x16,0xC,0x2,0x117,0x118,0x7,0x5,0x2,0x2,
		    0x118,0x11B,0x3,0x2,0x2,0x2,0x119,0x11B,0x7,0x6,0x2,0x2,
		    0x11A,0x10C,0x3,0x2,0x2,0x2,0x11A,0x110,0x3,0x2,0x2,0x2,
		    0x11A,0x111,0x3,0x2,0x2,0x2,0x11A,0x115,0x3,0x2,0x2,0x2,
		    0x11A,0x119,0x3,0x2,0x2,0x2,0x11B,0x27,0x3,0x2,0x2,0x2,
		    0x11C,0x11E,0x5,0x34,0x1B,0x2,0x11D,0x11F,0x5,0x2C,0x17,
		    0x2,0x11E,0x11D,0x3,0x2,0x2,0x2,0x11E,0x11F,0x3,0x2,0x2,
		    0x2,0x11F,0x12B,0x3,0x2,0x2,0x2,0x120,0x12B,0x5,0x30,0x19,
		    0x2,0x121,0x123,0x5,0x2C,0x17,0x2,0x122,0x124,0x5,0x34,
		    0x1B,0x2,0x123,0x122,0x3,0x2,0x2,0x2,0x123,0x124,0x3,0x2,
		    0x2,0x2,0x124,0x12B,0x3,0x2,0x2,0x2,0x125,0x126,0x7,0x4,
		    0x2,0x2,0x126,0x127,0x5,0x16,0xC,0x2,0x127,0x128,0x7,0x5,
		    0x2,0x2,0x128,0x12B,0x3,0x2,0x2,0x2,0x129,0x12B,0x7,0x6,
		    0x2,0x2,0x12A,0x11C,0x3,0x2,0x2,0x2,0x12A,0x120,0x3,0x2,
		    0x2,0x2,0x12A,0x121,0x3,0x2,0x2,0x2,0x12A,0x125,0x3,0x2,
		    0x2,0x2,0x12A,0x129,0x3,0x2,0x2,0x2,0x12B,0x29,0x3,0x2,
		    0x2,0x2,0x12C,0x12F,0x5,0x48,0x25,0x2,0x12D,0x12F,0x5,
		    0x2E,0x18,0x2,0x12E,0x12C,0x3,0x2,0x2,0x2,0x12E,0x12D,
		    0x3,0x2,0x2,0x2,0x12F,0x2B,0x3,0x2,0x2,0x2,0x130,0x133,
		    0x5,0x4A,0x26,0x2,0x131,0x133,0x5,0x2E,0x18,0x2,0x132,
		    0x130,0x3,0x2,0x2,0x2,0x132,0x131,0x3,0x2,0x2,0x2,0x133,
		    0x2D,0x3,0x2,0x2,0x2,0x134,0x139,0x7,0x3D,0x2,0x2,0x135,
		    0x139,0x7,0x3C,0x2,0x2,0x136,0x137,0x7,0x7,0x2,0x2,0x137,
		    0x139,0x5,0x86,0x44,0x2,0x138,0x134,0x3,0x2,0x2,0x2,0x138,
		    0x135,0x3,0x2,0x2,0x2,0x138,0x136,0x3,0x2,0x2,0x2,0x139,
		    0x2F,0x3,0x2,0x2,0x2,0x13A,0x13E,0x7,0x23,0x2,0x2,0x13B,
		    0x13D,0x5,0x3A,0x1E,0x2,0x13C,0x13B,0x3,0x2,0x2,0x2,0x13D,
		    0x140,0x3,0x2,0x2,0x2,0x13E,0x13C,0x3,0x2,0x2,0x2,0x13E,
		    0x13F,0x3,0x2,0x2,0x2,0x13F,0x15C,0x3,0x2,0x2,0x2,0x140,
		    0x13E,0x3,0x2,0x2,0x2,0x141,0x145,0x5,0x38,0x1D,0x2,0x142,
		    0x144,0x5,0x3C,0x1F,0x2,0x143,0x142,0x3,0x2,0x2,0x2,0x144,
		    0x147,0x3,0x2,0x2,0x2,0x145,0x143,0x3,0x2,0x2,0x2,0x145,
		    0x146,0x3,0x2,0x2,0x2,0x146,0x15C,0x3,0x2,0x2,0x2,0x147,
		    0x145,0x3,0x2,0x2,0x2,0x148,0x14C,0x5,0x84,0x43,0x2,0x149,
		    0x14B,0x5,0x3A,0x1E,0x2,0x14A,0x149,0x3,0x2,0x2,0x2,0x14B,
		    0x14E,0x3,0x2,0x2,0x2,0x14C,0x14A,0x3,0x2,0x2,0x2,0x14C,
		    0x14D,0x3,0x2,0x2,0x2,0x14D,0x15C,0x3,0x2,0x2,0x2,0x14E,
		    0x14C,0x3,0x2,0x2,0x2,0x14F,0x153,0x5,0x68,0x35,0x2,0x150,
		    0x152,0x5,0x3A,0x1E,0x2,0x151,0x150,0x3,0x2,0x2,0x2,0x152,
		    0x155,0x3,0x2,0x2,0x2,0x153,0x151,0x3,0x2,0x2,0x2,0x153,
		    0x154,0x3,0x2,0x2,0x2,0x154,0x15C,0x3,0x2,0x2,0x2,0x155,
		    0x153,0x3,0x2,0x2,0x2,0x156,0x158,0x5,0x40,0x21,0x2,0x157,
		    0x156,0x3,0x2,0x2,0x2,0x158,0x159,0x3,0x2,0x2,0x2,0x159,
		    0x157,0x3,0x2,0x2,0x2,0x159,0x15A,0x3,0x2,0x2,0x2,0x15A,
		    0x15C,0x3,0x2,0x2,0x2,0x15B,0x13A,0x3,0x2,0x2,0x2,0x15B,
		    0x141,0x3,0x2,0x2,0x2,0x15B,0x148,0x3,0x2,0x2,0x2,0x15B,
		    0x14F,0x3,0x2,0x2,0x2,0x15B,0x157,0x3,0x2,0x2,0x2,0x15C,
		    0x31,0x3,0x2,0x2,0x2,0x15D,0x161,0x5,0x30,0x19,0x2,0x15E,
		    0x160,0x5,0x7A,0x3E,0x2,0x15F,0x15E,0x3,0x2,0x2,0x2,0x160,
		    0x163,0x3,0x2,0x2,0x2,0x161,0x15F,0x3,0x2,0x2,0x2,0x161,
		    0x162,0x3,0x2,0x2,0x2,0x162,0x167,0x3,0x2,0x2,0x2,0x163,
		    0x161,0x3,0x2,0x2,0x2,0x164,0x166,0x5,0x7C,0x3F,0x2,0x165,
		    0x164,0x3,0x2,0x2,0x2,0x166,0x169,0x3,0x2,0x2,0x2,0x167,
		    0x165,0x3,0x2,0x2,0x2,0x167,0x168,0x3,0x2,0x2,0x2,0x168,
		    0x33,0x3,0x2,0x2,0x2,0x169,0x167,0x3,0x2,0x2,0x2,0x16A,
		    0x16E,0x5,0x38,0x1D,0x2,0x16B,0x16D,0x5,0x3C,0x1F,0x2,
		    0x16C,0x16B,0x3,0x2,0x2,0x2,0x16D,0x170,0x3,0x2,0x2,0x2,
		    0x16E,0x16C,0x3,0x2,0x2,0x2,0x16E,0x16F,0x3,0x2,0x2,0x2,
		    0x16F,0x177,0x3,0x2,0x2,0x2,0x170,0x16E,0x3,0x2,0x2,0x2,
		    0x171,0x173,0x5,0x3C,0x1F,0x2,0x172,0x171,0x3,0x2,0x2,
		    0x2,0x173,0x174,0x3,0x2,0x2,0x2,0x174,0x172,0x3,0x2,0x2,
		    0x2,0x174,0x175,0x3,0x2,0x2,0x2,0x175,0x177,0x3,0x2,0x2,
		    0x2,0x176,0x16A,0x3,0x2,0x2,0x2,0x176,0x172,0x3,0x2,0x2,
		    0x2,0x177,0x35,0x3,0x2,0x2,0x2,0x178,0x17C,0x5,0x34,0x1B,
		    0x2,0x179,0x17B,0x5,0x7A,0x3E,0x2,0x17A,0x179,0x3,0x2,
		    0x2,0x2,0x17B,0x17E,0x3,0x2,0x2,0x2,0x17C,0x17A,0x3,0x2,
		    0x2,0x2,0x17C,0x17D,0x3,0x2,0x2,0x2,0x17D,0x182,0x3,0x2,
		    0x2,0x2,0x17E,0x17C,0x3,0x2,0x2,0x2,0x17F,0x181,0x5,0x7C,
		    0x3F,0x2,0x180,0x17F,0x3,0x2,0x2,0x2,0x181,0x184,0x3,0x2,
		    0x2,0x2,0x182,0x180,0x3,0x2,0x2,0x2,0x182,0x183,0x3,0x2,
		    0x2,0x2,0x183,0x37,0x3,0x2,0x2,0x2,0x184,0x182,0x3,0x2,
		    0x2,0x2,0x185,0x186,0x9,0x2,0x2,0x2,0x186,0x39,0x3,0x2,
		    0x2,0x2,0x187,0x18A,0x5,0x3C,0x1F,0x2,0x188,0x18A,0x5,
		    0x40,0x21,0x2,0x189,0x187,0x3,0x2,0x2,0x2,0x189,0x188,
		    0x3,0x2,0x2,0x2,0x18A,0x3B,0x3,0x2,0x2,0x2,0x18B,0x18C,
		    0x5,0x3E,0x20,0x2,0x18C,0x18D,0x7,0x42,0x2,0x2,0x18D,0x193,
		    0x3,0x2,0x2,0x2,0x18E,0x190,0x7,0x3E,0x2,0x2,0x18F,0x191,
		    0x7,0x3F,0x2,0x2,0x190,0x18F,0x3,0x2,0x2,0x2,0x190,0x191,
		    0x3,0x2,0x2,0x2,0x191,0x193,0x3,0x2,0x2,0x2,0x192,0x18B,
		    0x3,0x2,0x2,0x2,0x192,0x18E,0x3,0x2,0x2,0x2,0x193,0x3D,
		    0x3,0x2,0x2,0x2,0x194,0x195,0x9,0x3,0x2,0x2,0x195,0x3F,
		    0x3,0x2,0x2,0x2,0x196,0x197,0x5,0x42,0x22,0x2,0x197,0x198,
		    0x5,0x46,0x24,0x2,0x198,0x19D,0x3,0x2,0x2,0x2,0x199,0x19A,
		    0x5,0x44,0x23,0x2,0x19A,0x19B,0x7,0x42,0x2,0x2,0x19B,0x19D,
		    0x3,0x2,0x2,0x2,0x19C,0x196,0x3,0x2,0x2,0x2,0x19C,0x199,
		    0x3,0x2,0x2,0x2,0x19D,0x41,0x3,0x2,0x2,0x2,0x19E,0x19F,
		    0x9,0x4,0x2,0x2,0x19F,0x43,0x3,0x2,0x2,0x2,0x1A0,0x1A1,
		    0x9,0x5,0x2,0x2,0x1A1,0x45,0x3,0x2,0x2,0x2,0x1A2,0x1A3,
		    0x9,0x6,0x2,0x2,0x1A3,0x47,0x3,0x2,0x2,0x2,0x1A4,0x1A8,
		    0x5,0x4A,0x26,0x2,0x1A5,0x1A7,0x5,0x7A,0x3E,0x2,0x1A6,
		    0x1A5,0x3,0x2,0x2,0x2,0x1A7,0x1AA,0x3,0x2,0x2,0x2,0x1A8,
		    0x1A6,0x3,0x2,0x2,0x2,0x1A8,0x1A9,0x3,0x2,0x2,0x2,0x1A9,
		    0x1AE,0x3,0x2,0x2,0x2,0x1AA,0x1A8,0x3,0x2,0x2,0x2,0x1AB,
		    0x1AD,0x5,0x7C,0x3F,0x2,0x1AC,0x1AB,0x3,0x2,0x2,0x2,0x1AD,
		    0x1B0,0x3,0x2,0x2,0x2,0x1AE,0x1AC,0x3,0x2,0x2,0x2,0x1AE,
		    0x1AF,0x3,0x2,0x2,0x2,0x1AF,0x49,0x3,0x2,0x2,0x2,0x1B0,
		    0x1AE,0x3,0x2,0x2,0x2,0x1B1,0x1B3,0x5,0x4C,0x27,0x2,0x1B2,
		    0x1B1,0x3,0x2,0x2,0x2,0x1B3,0x1B6,0x3,0x2,0x2,0x2,0x1B4,
		    0x1B2,0x3,0x2,0x2,0x2,0x1B4,0x1B5,0x3,0x2,0x2,0x2,0x1B5,
		    0x1B7,0x3,0x2,0x2,0x2,0x1B6,0x1B4,0x3,0x2,0x2,0x2,0x1B7,
		    0x1B9,0x7,0x8,0x2,0x2,0x1B8,0x1BA,0x5,0x50,0x29,0x2,0x1B9,
		    0x1B8,0x3,0x2,0x2,0x2,0x1B9,0x1BA,0x3,0x2,0x2,0x2,0x1BA,
		    0x1BB,0x3,0x2,0x2,0x2,0x1BB,0x1BC,0x7,0x9,0x2,0x2,0x1BC,
		    0x4B,0x3,0x2,0x2,0x2,0x1BD,0x1C1,0x5,0x98,0x4D,0x2,0x1BE,
		    0x1C1,0x5,0x4E,0x28,0x2,0x1BF,0x1C1,0x7,0x21,0x2,0x2,0x1C0,
		    0x1BD,0x3,0x2,0x2,0x2,0x1C0,0x1BE,0x3,0x2,0x2,0x2,0x1C0,
		    0x1BF,0x3,0x2,0x2,0x2,0x1C1,0x4D,0x3,0x2,0x2,0x2,0x1C2,
		    0x1C4,0x7,0x22,0x2,0x2,0x1C3,0x1C5,0x5,0x80,0x41,0x2,0x1C4,
		    0x1C3,0x3,0x2,0x2,0x2,0x1C5,0x1C6,0x3,0x2,0x2,0x2,0x1C6,
		    0x1C4,0x3,0x2,0x2,0x2,0x1C6,0x1C7,0x3,0x2,0x2,0x2,0x1C7,
		    0x4F,0x3,0x2,0x2,0x2,0x1C8,0x1C9,0x5,0x52,0x2A,0x2,0x1C9,
		    0x51,0x3,0x2,0x2,0x2,0x1CA,0x1CD,0x5,0x56,0x2C,0x2,0x1CB,
		    0x1CD,0x5,0x54,0x2B,0x2,0x1CC,0x1CA,0x3,0x2,0x2,0x2,0x1CC,
		    0x1CB,0x3,0x2,0x2,0x2,0x1CD,0x53,0x3,0x2,0x2,0x2,0x1CE,
		    0x1D1,0x5,0x56,0x2C,0x2,0x1CF,0x1D0,0x7,0xA,0x2,0x2,0x1D0,
		    0x1D2,0x5,0x56,0x2C,0x2,0x1D1,0x1CF,0x3,0x2,0x2,0x2,0x1D2,
		    0x1D3,0x3,0x2,0x2,0x2,0x1D3,0x1D1,0x3,0x2,0x2,0x2,0x1D3,
		    0x1D4,0x3,0x2,0x2,0x2,0x1D4,0x55,0x3,0x2,0x2,0x2,0x1D5,
		    0x1D8,0x5,0x58,0x2D,0x2,0x1D6,0x1D8,0x5,0x5A,0x2E,0x2,
		    0x1D7,0x1D5,0x3,0x2,0x2,0x2,0x1D7,0x1D6,0x3,0x2,0x2,0x2,
		    0x1D8,0x57,0x3,0x2,0x2,0x2,0x1D9,0x1DB,0x5,0x5C,0x2F,0x2,
		    0x1DA,0x1DC,0x7,0xB,0x2,0x2,0x1DB,0x1DA,0x3,0x2,0x2,0x2,
		    0x1DB,0x1DC,0x3,0x2,0x2,0x2,0x1DC,0x59,0x3,0x2,0x2,0x2,
		    0x1DD,0x1E0,0x5,0x5C,0x2F,0x2,0x1DE,0x1DF,0x7,0xB,0x2,
		    0x2,0x1DF,0x1E1,0x5,0x5C,0x2F,0x2,0x1E0,0x1DE,0x3,0x2,
		    0x2,0x2,0x1E1,0x1E2,0x3,0x2,0x2,0x2,0x1E2,0x1E0,0x3,0x2,
		    0x2,0x2,0x1E2,0x1E3,0x3,0x2,0x2,0x2,0x1E3,0x1E5,0x3,0x2,
		    0x2,0x2,0x1E4,0x1E6,0x7,0xB,0x2,0x2,0x1E5,0x1E4,0x3,0x2,
		    0x2,0x2,0x1E5,0x1E6,0x3,0x2,0x2,0x2,0x1E6,0x5B,0x3,0x2,
		    0x2,0x2,0x1E7,0x1E8,0x7,0xC,0x2,0x2,0x1E8,0x1EA,0x5,0x88,
		    0x45,0x2,0x1E9,0x1E7,0x3,0x2,0x2,0x2,0x1E9,0x1EA,0x3,0x2,
		    0x2,0x2,0x1EA,0x1ED,0x3,0x2,0x2,0x2,0x1EB,0x1EE,0x5,0x60,
		    0x31,0x2,0x1EC,0x1EE,0x5,0x5E,0x30,0x2,0x1ED,0x1EB,0x3,
		    0x2,0x2,0x2,0x1ED,0x1EC,0x3,0x2,0x2,0x2,0x1EE,0x1F1,0x3,
		    0x2,0x2,0x2,0x1EF,0x1F1,0x5,0x78,0x3D,0x2,0x1F0,0x1E9,
		    0x3,0x2,0x2,0x2,0x1F0,0x1EF,0x3,0x2,0x2,0x2,0x1F1,0x5D,
		    0x3,0x2,0x2,0x2,0x1F2,0x1F3,0x7,0x4,0x2,0x2,0x1F3,0x1F4,
		    0x5,0x50,0x29,0x2,0x1F4,0x1F6,0x7,0x5,0x2,0x2,0x1F5,0x1F7,
		    0x5,0x62,0x32,0x2,0x1F6,0x1F5,0x3,0x2,0x2,0x2,0x1F6,0x1F7,
		    0x3,0x2,0x2,0x2,0x1F7,0x1FB,0x3,0x2,0x2,0x2,0x1F8,0x1FA,
		    0x5,0x7A,0x3E,0x2,0x1F9,0x1F8,0x3,0x2,0x2,0x2,0x1FA,0x1FD,
		    0x3,0x2,0x2,0x2,0x1FB,0x1F9,0x3,0x2,0x2,0x2,0x1FB,0x1FC,
		    0x3,0x2,0x2,0x2,0x1FC,0x201,0x3,0x2,0x2,0x2,0x1FD,0x1FB,
		    0x3,0x2,0x2,0x2,0x1FE,0x200,0x5,0x7C,0x3F,0x2,0x1FF,0x1FE,
		    0x3,0x2,0x2,0x2,0x200,0x203,0x3,0x2,0x2,0x2,0x201,0x1FF,
		    0x3,0x2,0x2,0x2,0x201,0x202,0x3,0x2,0x2,0x2,0x202,0x5F,
		    0x3,0x2,0x2,0x2,0x203,0x201,0x3,0x2,0x2,0x2,0x204,0x206,
		    0x5,0x66,0x34,0x2,0x205,0x204,0x3,0x2,0x2,0x2,0x205,0x206,
		    0x3,0x2,0x2,0x2,0x206,0x207,0x3,0x2,0x2,0x2,0x207,0x208,
		    0x5,0x80,0x41,0x2,0x208,0x20A,0x5,0x18,0xD,0x2,0x209,0x20B,
		    0x5,0x62,0x32,0x2,0x20A,0x209,0x3,0x2,0x2,0x2,0x20A,0x20B,
		    0x3,0x2,0x2,0x2,0x20B,0x20F,0x3,0x2,0x2,0x2,0x20C,0x20E,
		    0x5,0x7A,0x3E,0x2,0x20D,0x20C,0x3,0x2,0x2,0x2,0x20E,0x211,
		    0x3,0x2,0x2,0x2,0x20F,0x20D,0x3,0x2,0x2,0x2,0x20F,0x210,
		    0x3,0x2,0x2,0x2,0x210,0x215,0x3,0x2,0x2,0x2,0x211,0x20F,
		    0x3,0x2,0x2,0x2,0x212,0x214,0x5,0x7C,0x3F,0x2,0x213,0x212,
		    0x3,0x2,0x2,0x2,0x214,0x217,0x3,0x2,0x2,0x2,0x215,0x213,
		    0x3,0x2,0x2,0x2,0x215,0x216,0x3,0x2,0x2,0x2,0x216,0x61,
		    0x3,0x2,0x2,0x2,0x217,0x215,0x3,0x2,0x2,0x2,0x218,0x21D,
		    0x7,0x46,0x2,0x2,0x219,0x21D,0x7,0xD,0x2,0x2,0x21A,0x21D,
		    0x7,0xE,0x2,0x2,0x21B,0x21D,0x5,0x64,0x33,0x2,0x21C,0x218,
		    0x3,0x2,0x2,0x2,0x21C,0x219,0x3,0x2,0x2,0x2,0x21C,0x21A,
		    0x3,0x2,0x2,0x2,0x21C,0x21B,0x3,0x2,0x2,0x2,0x21D,0x63,
		    0x3,0x2,0x2,0x2,0x21E,0x21F,0x7,0x8,0x2,0x2,0x21F,0x220,
		    0x7,0x42,0x2,0x2,0x220,0x229,0x7,0x9,0x2,0x2,0x221,0x222,
		    0x7,0x8,0x2,0x2,0x222,0x223,0x7,0x42,0x2,0x2,0x223,0x225,
		    0x7,0xF,0x2,0x2,0x224,0x226,0x9,0x7,0x2,0x2,0x225,0x224,
		    0x3,0x2,0x2,0x2,0x225,0x226,0x3,0x2,0x2,0x2,0x226,0x227,
		    0x3,0x2,0x2,0x2,0x227,0x229,0x7,0x9,0x2,0x2,0x228,0x21E,
		    0x3,0x2,0x2,0x2,0x228,0x221,0x3,0x2,0x2,0x2,0x229,0x65,
		    0x3,0x2,0x2,0x2,0x22A,0x22B,0x7,0x10,0x2,0x2,0x22B,0x67,
		    0x3,0x2,0x2,0x2,0x22C,0x230,0x7,0x11,0x2,0x2,0x22D,0x22F,
		    0x5,0x6A,0x36,0x2,0x22E,0x22D,0x3,0x2,0x2,0x2,0x22F,0x232,
		    0x3,0x2,0x2,0x2,0x230,0x22E,0x3,0x2,0x2,0x2,0x230,0x231,
		    0x3,0x2,0x2,0x2,0x231,0x233,0x3,0x2,0x2,0x2,0x232,0x230,
		    0x3,0x2,0x2,0x2,0x233,0x234,0x7,0x12,0x2,0x2,0x234,0x69,
		    0x3,0x2,0x2,0x2,0x235,0x24B,0x5,0x6C,0x37,0x2,0x236,0x24B,
		    0x5,0x70,0x39,0x2,0x237,0x24B,0x5,0x74,0x3B,0x2,0x238,
		    0x248,0x7,0x6,0x2,0x2,0x239,0x23B,0x5,0x6E,0x38,0x2,0x23A,
		    0x239,0x3,0x2,0x2,0x2,0x23B,0x23C,0x3,0x2,0x2,0x2,0x23C,
		    0x23A,0x3,0x2,0x2,0x2,0x23C,0x23D,0x3,0x2,0x2,0x2,0x23D,
		    0x249,0x3,0x2,0x2,0x2,0x23E,0x240,0x5,0x72,0x3A,0x2,0x23F,
		    0x23E,0x3,0x2,0x2,0x2,0x240,0x241,0x3,0x2,0x2,0x2,0x241,
		    0x23F,0x3,0x2,0x2,0x2,0x241,0x242,0x3,0x2,0x2,0x2,0x242,
		    0x249,0x3,0x2,0x2,0x2,0x243,0x245,0x5,0x76,0x3C,0x2,0x244,
		    0x243,0x3,0x2,0x2,0x2,0x245,0x246,0x3,0x2,0x2,0x2,0x246,
		    0x244,0x3,0x2,0x2,0x2,0x246,0x247,0x3,0x2,0x2,0x2,0x247,
		    0x249,0x3,0x2,0x2,0x2,0x248,0x23A,0x3,0x2,0x2,0x2,0x248,
		    0x23F,0x3,0x2,0x2,0x2,0x248,0x244,0x3,0x2,0x2,0x2,0x249,
		    0x24B,0x3,0x2,0x2,0x2,0x24A,0x235,0x3,0x2,0x2,0x2,0x24A,
		    0x236,0x3,0x2,0x2,0x2,0x24A,0x237,0x3,0x2,0x2,0x2,0x24A,
		    0x238,0x3,0x2,0x2,0x2,0x24B,0x6B,0x3,0x2,0x2,0x2,0x24C,
		    0x254,0x5,0x92,0x4A,0x2,0x24D,0x251,0x7,0x45,0x2,0x2,0x24E,
		    0x250,0x5,0x6E,0x38,0x2,0x24F,0x24E,0x3,0x2,0x2,0x2,0x250,
		    0x253,0x3,0x2,0x2,0x2,0x251,0x24F,0x3,0x2,0x2,0x2,0x251,
		    0x252,0x3,0x2,0x2,0x2,0x252,0x255,0x3,0x2,0x2,0x2,0x253,
		    0x251,0x3,0x2,0x2,0x2,0x254,0x24D,0x3,0x2,0x2,0x2,0x254,
		    0x255,0x3,0x2,0x2,0x2,0x255,0x6D,0x3,0x2,0x2,0x2,0x256,
		    0x257,0x7,0x13,0x2,0x2,0x257,0x259,0x5,0x92,0x4A,0x2,0x258,
		    0x25A,0x7,0x45,0x2,0x2,0x259,0x258,0x3,0x2,0x2,0x2,0x259,
		    0x25A,0x3,0x2,0x2,0x2,0x25A,0x6F,0x3,0x2,0x2,0x2,0x25B,
		    0x263,0x5,0x7E,0x40,0x2,0x25C,0x260,0x7,0x45,0x2,0x2,0x25D,
		    0x25F,0x5,0x72,0x3A,0x2,0x25E,0x25D,0x3,0x2,0x2,0x2,0x25F,
		    0x262,0x3,0x2,0x2,0x2,0x260,0x25E,0x3,0x2,0x2,0x2,0x260,
		    0x261,0x3,0x2,0x2,0x2,0x261,0x264,0x3,0x2,0x2,0x2,0x262,
		    0x260,0x3,0x2,0x2,0x2,0x263,0x25C,0x3,0x2,0x2,0x2,0x263,
		    0x264,0x3,0x2,0x2,0x2,0x264,0x71,0x3,0x2,0x2,0x2,0x265,
		    0x266,0x7,0x13,0x2,0x2,0x266,0x268,0x5,0x7E,0x40,0x2,0x267,
		    0x269,0x7,0x45,0x2,0x2,0x268,0x267,0x3,0x2,0x2,0x2,0x268,
		    0x269,0x3,0x2,0x2,0x2,0x269,0x73,0x3,0x2,0x2,0x2,0x26A,
		    0x272,0x7,0x41,0x2,0x2,0x26B,0x26F,0x7,0x45,0x2,0x2,0x26C,
		    0x26E,0x5,0x76,0x3C,0x2,0x26D,0x26C,0x3,0x2,0x2,0x2,0x26E,
		    0x271,0x3,0x2,0x2,0x2,0x26F,0x26D,0x3,0x2,0x2,0x2,0x26F,
		    0x270,0x3,0x2,0x2,0x2,0x270,0x273,0x3,0x2,0x2,0x2,0x271,
		    0x26F,0x3,0x2,0x2,0x2,0x272,0x26B,0x3,0x2,0x2,0x2,0x272,
		    0x273,0x3,0x2,0x2,0x2,0x273,0x27D,0x3,0x2,0x2,0x2,0x274,
		    0x275,0x7,0x7,0x2,0x2,0x275,0x279,0x7,0x45,0x2,0x2,0x276,
		    0x278,0x5,0x76,0x3C,0x2,0x277,0x276,0x3,0x2,0x2,0x2,0x278,
		    0x27B,0x3,0x2,0x2,0x2,0x279,0x277,0x3,0x2,0x2,0x2,0x279,
		    0x27A,0x3,0x2,0x2,0x2,0x27A,0x27D,0x3,0x2,0x2,0x2,0x27B,
		    0x279,0x3,0x2,0x2,0x2,0x27C,0x26A,0x3,0x2,0x2,0x2,0x27C,
		    0x274,0x3,0x2,0x2,0x2,0x27D,0x75,0x3,0x2,0x2,0x2,0x27E,
		    0x27F,0x7,0x13,0x2,0x2,0x27F,0x281,0x7,0x41,0x2,0x2,0x280,
		    0x282,0x7,0x45,0x2,0x2,0x281,0x280,0x3,0x2,0x2,0x2,0x281,
		    0x282,0x3,0x2,0x2,0x2,0x282,0x77,0x3,0x2,0x2,0x2,0x283,
		    0x284,0x7,0x14,0x2,0x2,0x284,0x285,0x5,0x88,0x45,0x2,0x285,
		    0x79,0x3,0x2,0x2,0x2,0x286,0x287,0x7,0x15,0x2,0x2,0x287,
		    0x28A,0x5,0x80,0x41,0x2,0x288,0x28B,0x5,0x92,0x4A,0x2,
		    0x289,0x28B,0x5,0x7E,0x40,0x2,0x28A,0x288,0x3,0x2,0x2,
		    0x2,0x28A,0x289,0x3,0x2,0x2,0x2,0x28B,0x7B,0x3,0x2,0x2,
		    0x2,0x28C,0x28D,0x7,0x16,0x2,0x2,0x28D,0x28E,0x5,0x92,
		    0x4A,0x2,0x28E,0x28F,0x9,0x8,0x2,0x2,0x28F,0x7D,0x3,0x2,
		    0x2,0x2,0x290,0x294,0x5,0x8C,0x47,0x2,0x291,0x294,0x5,
		    0x8A,0x46,0x2,0x292,0x294,0x5,0x8E,0x48,0x2,0x293,0x290,
		    0x3,0x2,0x2,0x2,0x293,0x291,0x3,0x2,0x2,0x2,0x293,0x292,
		    0x3,0x2,0x2,0x2,0x294,0x7F,0x3,0x2,0x2,0x2,0x295,0x298,
		    0x5,0x92,0x4A,0x2,0x296,0x298,0x5,0x82,0x42,0x2,0x297,
		    0x295,0x3,0x2,0x2,0x2,0x297,0x296,0x3,0x2,0x2,0x2,0x298,
		    0x81,0x3,0x2,0x2,0x2,0x299,0x29A,0x7,0x38,0x2,0x2,0x29A,
		    0x83,0x3,0x2,0x2,0x2,0x29B,0x29C,0x5,0x92,0x4A,0x2,0x29C,
		    0x85,0x3,0x2,0x2,0x2,0x29D,0x2A0,0x5,0x92,0x4A,0x2,0x29E,
		    0x2A0,0x5,0x96,0x4C,0x2,0x29F,0x29D,0x3,0x2,0x2,0x2,0x29F,
		    0x29E,0x3,0x2,0x2,0x2,0x2A0,0x87,0x3,0x2,0x2,0x2,0x2A1,
		    0x2A4,0x5,0x92,0x4A,0x2,0x2A2,0x2A4,0x5,0x96,0x4C,0x2,
		    0x2A3,0x2A1,0x3,0x2,0x2,0x2,0x2A3,0x2A2,0x3,0x2,0x2,0x2,
		    0x2A4,0x89,0x3,0x2,0x2,0x2,0x2A5,0x2A6,0x9,0x6,0x2,0x2,
		    0x2A6,0x8B,0x3,0x2,0x2,0x2,0x2A7,0x2AB,0x5,0x90,0x49,0x2,
		    0x2A8,0x2AC,0x7,0x41,0x2,0x2,0x2A9,0x2AA,0x7,0x17,0x2,
		    0x2,0x2AA,0x2AC,0x5,0x84,0x43,0x2,0x2AB,0x2A8,0x3,0x2,
		    0x2,0x2,0x2AB,0x2A9,0x3,0x2,0x2,0x2,0x2AB,0x2AC,0x3,0x2,
		    0x2,0x2,0x2AC,0x8D,0x3,0x2,0x2,0x2,0x2AD,0x2AE,0x9,0x9,
		    0x2,0x2,0x2AE,0x8F,0x3,0x2,0x2,0x2,0x2AF,0x2B0,0x9,0xA,
		    0x2,0x2,0x2B0,0x91,0x3,0x2,0x2,0x2,0x2B1,0x2B4,0x7,0x39,
		    0x2,0x2,0x2B2,0x2B4,0x5,0x94,0x4B,0x2,0x2B3,0x2B1,0x3,
		    0x2,0x2,0x2,0x2B3,0x2B2,0x3,0x2,0x2,0x2,0x2B4,0x93,0x3,
		    0x2,0x2,0x2,0x2B5,0x2B6,0x9,0xB,0x2,0x2,0x2B6,0x95,0x3,
		    0x2,0x2,0x2,0x2B7,0x2B8,0x7,0x40,0x2,0x2,0x2B8,0x97,0x3,
		    0x2,0x2,0x2,0x2B9,0x2BA,0x7,0x1A,0x2,0x2,0x2BA,0x2BE,0x5,
		    0x86,0x44,0x2,0x2BB,0x2BC,0x7,0x14,0x2,0x2,0x2BC,0x2BE,
		    0x5,0x86,0x44,0x2,0x2BD,0x2B9,0x3,0x2,0x2,0x2,0x2BD,0x2BB,
		    0x3,0x2,0x2,0x2,0x2BE,0x99,0x3,0x2,0x2,0x2,0x2BF,0x2C0,
		    0x7,0x1C,0x2,0x2,0x2C0,0x2C4,0x5,0x86,0x44,0x2,0x2C1,0x2C2,
		    0x7,0x13,0x2,0x2,0x2C2,0x2C4,0x5,0x86,0x44,0x2,0x2C3,0x2BF,
		    0x3,0x2,0x2,0x2,0x2C3,0x2C1,0x3,0x2,0x2,0x2,0x2C4,0x9B,
		    0x3,0x2,0x2,0x2,0x60,0x9F,0xA4,0xA9,0xAC,0xB3,0xC1,0xCA,
		    0xCE,0xD1,0xD7,0xDC,0xE7,0xEF,0xF7,0xFF,0x103,0x108,0x10E,
		    0x113,0x11A,0x11E,0x123,0x12A,0x12E,0x132,0x138,0x13E,0x145,
		    0x14C,0x153,0x159,0x15B,0x161,0x167,0x16E,0x174,0x176,0x17C,
		    0x182,0x189,0x190,0x192,0x19C,0x1A8,0x1AE,0x1B4,0x1B9,0x1C0,
		    0x1C6,0x1CC,0x1D3,0x1D7,0x1DB,0x1E2,0x1E5,0x1E9,0x1ED,0x1F0,
		    0x1F6,0x1FB,0x201,0x205,0x20A,0x20F,0x215,0x21C,0x225,0x228,
		    0x230,0x23C,0x241,0x246,0x248,0x24A,0x251,0x254,0x259,0x260,
		    0x263,0x268,0x26F,0x272,0x279,0x27C,0x281,0x28A,0x293,0x297,
		    0x29F,0x2A3,0x2AB,0x2B3,0x2BD,0x2C3];

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