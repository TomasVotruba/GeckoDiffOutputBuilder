<?php

declare(strict_types=1);

/*
 * This file is part of the GeckoPackages.
 *
 * (c) GeckoPackages https://github.com/GeckoPackages
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace GeckoPackages\DiffOutputBuilder\Tests;

use GeckoPackages\DiffOutputBuilder\Utils\UnifiedDiffAssertTrait;
use PHPUnit\Framework\TestCase;

/**
 * @author SpacePossum
 *
 * @covers \GeckoPackages\DiffOutputBuilder\Utils\UnifiedDiffAssertTrait
 *
 * @internal
 */
final class UnifiedDiffAssertTraitTest extends TestCase
{
    use UnifiedDiffAssertTrait;

    /**
     * @param string $diff
     *
     * @dataProvider provideValidCases
     */
    public function testValidCases(string $diff)
    {
        $this->assertValidUnifiedDiffFormat($diff);
    }

    public function provideValidCases(): array
    {
        return [
            [
'--- Original
+++ New
@@ -8 +8 @@
-Z
+U
',
            ],
            [
'--- Original
+++ New
@@ -8 +8 @@
-Z
+U
@@ -15 +15 @@
-X
+V
',
            ],
            'empty diff. is valid' => [
                '',
            ],
        ];
    }

    public function testNoLinebreakEnd()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageRegExp(\sprintf('#^%s$#', \preg_quote('Expected diff to end with a line break, got "C".', '#')));

        $this->assertValidUnifiedDiffFormat("A\nB\nC");
    }

    public function testInvalidStartWithoutHeader()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageRegExp(\sprintf('#^%s$#', \preg_quote("Expected line to start with '@', '-' or '+', got \"A\n\". Line 1.", '#')));

        $this->assertValidUnifiedDiffFormat("A\n");
    }

    public function testInvalidStartHeader1()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageRegExp(\sprintf('#^%s$#', \preg_quote("Line 1 indicates a header, so line 2 must start with \"+++\".\nLine 1: \"--- A\n\"\nLine 2: \"+ 1\n\".", '#')));

        $this->assertValidUnifiedDiffFormat("--- A\n+ 1\n");
    }

    public function testInvalidStartHeader2()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageRegExp(\sprintf('#^%s$#', \preg_quote("Header line does not match expected pattern, got \"+++ file	X\n\". Line 2.", '#')));

        $this->assertValidUnifiedDiffFormat("--- A\n+++ file\tX\n");
    }

    public function testInvalidStartHeader3()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageRegExp(\sprintf('#^%s$#', \preg_quote('Date of header line does not match expected pattern, got "[invalid date]". Line 1.', '#')));

        $this->assertValidUnifiedDiffFormat(
"--- Original\t[invalid date]
+++ New
@@ -1,2 +1,2 @@
-A
+B
 ".'
');
    }

    public function testInvalidStartHeader4()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageRegExp(\sprintf('#^%s$#', \preg_quote("Expected header line to start with \"+++  \", got \"+++INVALID\n\". Line 2.", '#')));

        $this->assertValidUnifiedDiffFormat(
'--- Original
+++INVALID
@@ -1,2 +1,2 @@
-A
+B
 '.'
');
    }

    public function testInvalidLine1()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageRegExp(\sprintf('#^%s$#', \preg_quote("Expected line to start with '@', '-' or '+', got \"1\n\". Line 5.", '#')));

        $this->assertValidUnifiedDiffFormat(
'--- Original
+++ New
@@ -8 +8 @@
-Z
1
+U
');
    }

    public function testInvalidLine2()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageRegExp(\sprintf('#^%s$#', \preg_quote('Expected string length of minimal 2, got 1. Line 4.', '#')));

        $this->assertValidUnifiedDiffFormat(
'--- Original
+++ New
@@ -8 +8 @@


');
    }

    public function testHunkInvalidFormat()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageRegExp(\sprintf('#^%s$#', \preg_quote("Hunk header line does not match expected pattern, got \"@@ INVALID -1,1 +1,1 @@\n\". Line 3.", '#')));

        $this->assertValidUnifiedDiffFormat(
'--- Original
+++ New
@@ INVALID -1,1 +1,1 @@
-Z
+U
');
    }

    public function testHunkOverlapFrom()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageRegExp(\sprintf('#^%s$#', \preg_quote('Unexpected new hunk; "from" (\'-\') start overlaps previous hunk. Line 6.', '#')));

        $this->assertValidUnifiedDiffFormat(
'--- Original
+++ New
@@ -8,1 +8,1 @@
-Z
+U
@@ -7,1 +9,1 @@
-Z
+U
');
    }

    public function testHunkOverlapTo()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageRegExp(\sprintf('#^%s$#', \preg_quote('Unexpected new hunk; "to" (\'+\') start overlaps previous hunk. Line 6.', '#')));

        $this->assertValidUnifiedDiffFormat(
'--- Original
+++ New
@@ -8,1 +8,1 @@
-Z
+U
@@ -17,1 +7,1 @@
-Z
+U
');
    }

    public function testExpectHunk1()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageRegExp(\sprintf('#^%s$#', \preg_quote('Expected hunk start (\'@\'), got "+". Line 6.', '#')));

        $this->assertValidUnifiedDiffFormat(
'--- Original
+++ New
@@ -8 +8 @@
-Z
+U
+O
');
    }

    public function testExpectHunk2()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageRegExp(\sprintf('#^%s$#', \preg_quote('Unexpected hunk start (\'@\'). Line 6.', '#')));

        $this->assertValidUnifiedDiffFormat(
'--- Original
+++ New
@@ -8,12 +8,12 @@
 '.'
 '.'
@@ -38,12 +48,12 @@
');
    }

    public function testMisplacedLineAfterComments1()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageRegExp(\sprintf('#^%s$#', \preg_quote('Unexpected line as 2 "No newline" markers have found, ". Line 8.', '#')));

        $this->assertValidUnifiedDiffFormat(
'--- Original
+++ New
@@ -8 +8 @@
-Z
\ No newline at end of file
+U
\ No newline at end of file
+A
');
    }

    public function testMisplacedLineAfterComments2()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageRegExp(\sprintf('#^%s$#', \preg_quote('Unexpected line as 2 "No newline" markers have found, ". Line 7.', '#')));

        $this->assertValidUnifiedDiffFormat(
'--- Original
+++ New
@@ -8 +8 @@
+U
\ No newline at end of file
\ No newline at end of file
\ No newline at end of file
');
    }

    public function testMisplacedLineAfterComments3()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageRegExp(\sprintf('#^%s$#', \preg_quote('Unexpected line as 2 "No newline" markers have found, ". Line 7.', '#')));

        $this->assertValidUnifiedDiffFormat(
'--- Original
+++ New
@@ -8 +8 @@
+U
\ No newline at end of file
\ No newline at end of file
+A
');
    }

    public function testMisplacedComment()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageRegExp(\sprintf('#^%s$#', \preg_quote('Unexpected "\ No newline at end of file", it must be preceded by \'+\' or \'-\' line. Line 1.', '#')));

        $this->assertValidUnifiedDiffFormat(
'\ No newline at end of file
');
    }

    public function testUnexpectedDuplicateNoNewLineEOF()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageRegExp(\sprintf('#^%s$#', \preg_quote('Unexpected "\\ No newline at end of file", "\\" was already closed. Line 8.', '#')));

        $this->assertValidUnifiedDiffFormat(
'--- Original
+++ New
@@ -8,12 +8,12 @@
 '.'
 '.'
\ No newline at end of file
 '.'
\ No newline at end of file
');
    }

    public function testFromAfterClose()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageRegExp(\sprintf('#^%s$#', \preg_quote('Not expected from (\'-\'), already closed by "\ No newline at end of file". Line 6.', '#')));

        $this->assertValidUnifiedDiffFormat(
'--- Original
+++ New
@@ -8,12 +8,12 @@
-A
\ No newline at end of file
-A
\ No newline at end of file
');
    }

    public function testSameAfterFromClose()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageRegExp(\sprintf('#^%s$#', \preg_quote('Not expected same (\' \'), \'-\' already closed by "\ No newline at end of file". Line 6.', '#')));

        $this->assertValidUnifiedDiffFormat(
            '--- Original
+++ New
@@ -8,12 +8,12 @@
-A
\ No newline at end of file
 A
\ No newline at end of file
');
    }

    public function testToAfterClose()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageRegExp(\sprintf('#^%s$#', \preg_quote('Not expected to (\'+\'), already closed by "\ No newline at end of file". Line 6.', '#')));

        $this->assertValidUnifiedDiffFormat(
            '--- Original
+++ New
@@ -8,12 +8,12 @@
+A
\ No newline at end of file
+A
\ No newline at end of file
');
    }

    public function testSameAfterToClose()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageRegExp(\sprintf('#^%s$#', \preg_quote('Not expected same (\' \'), \'+\' already closed by "\ No newline at end of file". Line 6.', '#')));

        $this->assertValidUnifiedDiffFormat(
            '--- Original
+++ New
@@ -8,12 +8,12 @@
+A
\ No newline at end of file
 A
\ No newline at end of file
');
    }

    public function testUnexpectedEOFFromMissingLines()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageRegExp(\sprintf('#^%s$#', \preg_quote('Unexpected EOF, number of lines in hunk "from" (\'-\')) mismatched. Line 7.', '#')));

        $this->assertValidUnifiedDiffFormat(
'--- Original
+++ New
@@ -8,19 +7,2 @@
-A
+B
 '.'
');
    }

    public function testUnexpectedEOFToMissingLines()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageRegExp(\sprintf('#^%s$#', \preg_quote('Unexpected EOF, number of lines in hunk "to" (\'+\')) mismatched. Line 7.', '#')));

        $this->assertValidUnifiedDiffFormat(
'--- Original
+++ New
@@ -8,2 +7,3 @@
-A
+B
 '.'
');
    }

    public function testUnexpectedEOFBothFromAndToMissingLines()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageRegExp(\sprintf('#^%s$#', \preg_quote('Unexpected EOF, number of lines in hunk "from" (\'-\')) and "to" (\'+\') mismatched. Line 7.', '#')));

        $this->assertValidUnifiedDiffFormat(
'--- Original
+++ New
@@ -1,12 +1,14 @@
-A
+B
 '.'
');
    }
}
