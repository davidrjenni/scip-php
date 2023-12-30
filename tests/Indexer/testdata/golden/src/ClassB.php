  <?php
  
  declare(strict_types=1);
  
  namespace TestData;
  
  final class ClassB
//            ^^^^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassB#
//            documentation
//            > ```php
//            > final class ClassB
//            > ```
  {
      public const B1 = 42;
//                 ^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassB#B1.
//                 documentation
//                 > ```php
//                 > public B1 = 42
//                 > ```
  
      public static ?ClassC $b1;
//                   ^^^^^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassC#
//                          ^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassB#$b1.
//                          documentation
//                          > ```php
//                          > public static ?\TestData\ClassC $b1
//                          > ```
  
      public int $b2;
//               ^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassB#$b2.
//               documentation
//               > ```php
//               > public int $b2
//               > ```
  
      public function b1(int $p1, ClassD|ClassF $p2): int
//                    ^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassB#b1().
//                    documentation
//                    > ```php
//                    > public function b1(int $p1, \TestData\ClassD|\TestData\ClassF $p2): int
//                    > ```
//                           ^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassB#b1().($p1)
//                           documentation
//                           > ```php
//                           > int $p1
//                           > ```
//                                ^^^^^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassD#
//                                       ^^^^^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassF#
//                                              ^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassB#b1().($p2)
//                                              documentation
//                                              > ```php
//                                              > \TestData\ClassD|\TestData\ClassF $p2
//                                              > ```
      {
          return self::$b1->c2->d2 * $p1;
//               ^^^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassB#
//                     ^^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassB#$b1.
//                          ^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassC#$c2.
//                              ^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassD#$d2.
      }
  
      public function b2(): void
//                    ^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassB#b2().
//                    documentation
//                    > ```php
//                    > public function b2(): void
//                    > ```
      {
          \TestData\ClassJ::J0;
//        ^^^^^^^^^^^^^^^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassJ#
//                          ^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassJ#J0.
          \TestData3\ClassJ::J1;
//        ^^^^^^^^^^^^^^^^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData3/ClassJ#
//                           ^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData3/ClassJ#J1.
          \TestData2\ClassJ::J2;
//        ^^^^^^^^^^^^^^^^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData2/ClassJ#
//                           ^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData2/ClassJ#J2.
          \ClassJ::J3;
//        ^^^^^^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 ClassJ#
//                 ^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 ClassJ#J3.
      }
  }
  
