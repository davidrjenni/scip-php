  <?php
  
  declare(strict_types=1);
  
  namespace TestData;
  
  use function strlen;
//             ^^^^^^ reference scip-php composer php 8.3.22 strlen().
  
  /** @method ClassA m1(int $p1, $p2, bool $p3) */
//                   ^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassF#m1().
  final class ClassF
//            ^^^^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassF#
//            documentation
//            > ```php
//            > final class ClassF
//            > ```
//            documentation
//            > @method ClassA m1(int $p1, $p2, bool $p3)
  {
      public readonly int $f1;
//                        ^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassF#$f1.
//                        documentation
//                        > ```php
//                        > public readonly int $f1
//                        > ```
  
      public EnumG $f2;
//           ^^^^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/EnumG#
//                 ^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassF#$f2.
//                 documentation
//                 > ```php
//                 > public \TestData\EnumG $f2
//                 > ```
  
      private static ClassA $f3;
//                   ^^^^^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassA#
//                          ^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassF#$f3.
//                          documentation
//                          > ```php
//                          > private static \TestData\ClassA $f3
//                          > ```
  
      public function f1(): int
//                    ^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassF#f1().
//                    documentation
//                    > ```php
//                    > public function f1(): int
//                    > ```
      {
          return $this->f1 + 42 + strlen('ABC');
//                      ^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassF#$f1.
//                                ^^^^^^ reference scip-php composer php 8.3.22 strlen().
      }
  
      public static function f2(): ClassA
//                           ^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassF#f2().
//                           documentation
//                           > ```php
//                           > public static function f2(): \TestData\ClassA
//                           > ```
//                                 ^^^^^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassA#
      {
          return self::$f3;
//               ^^^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassF#
//                     ^^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassF#$f3.
      }
  }
  
  namespace TestData3;
  
  final class ClassJ
//            ^^^^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData3/ClassJ#
//            documentation
//            > ```php
//            > final class ClassJ
//            > ```
  {
      public const J1 = 42;
//                 ^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData3/ClassJ#J1.
//                 documentation
//                 > ```php
//                 > public J1 = 42
//                 > ```
  }
  
