  <?php
  
  declare(strict_types=1);
  
  namespace TestData;
  
  /**
   * @property              $d3
   * @property-read  ClassB $d4
//                          ^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassD#$d4.
   * @property-write ClassA $d5
//                          ^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassD#$d5.
   */
  final class ClassD extends ClassA
//            ^^^^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassD#
//            documentation
//            > ```php
//            > final class ClassD extends TestData\ClassA
//            > ```
//            documentation
//            > @property              $d3<br>@property-read  ClassB $d4<br>@property-write ClassA $d5<br>
//                           ^^^^^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassA#
  {
  
      public function __construct(
//                    ^^^^^^^^^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassD#__construct().
//                    documentation
//                    > ```php
//                    > public function __construct(public readonly \TestData\ClassF $d1, public readonly int $d2)
//                    > ```
          public readonly ClassF $d1,
//                        ^^^^^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassF#
//                               ^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassD#$d1.
//                               documentation
//                               > ```php
//                               > public readonly \TestData\ClassF $d1
//                               > ```
          public readonly int $d2,
//                            ^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassD#$d2.
//                            documentation
//                            > ```php
//                            > public readonly int $d2
//                            > ```
      ) {
      }
  }
  
  final class ClassJ
//            ^^^^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassJ#
//            documentation
//            > ```php
//            > final class ClassJ
//            > ```
  {
      public const J0 = 42;
//                 ^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassJ#J0.
//                 documentation
//                 > ```php
//                 > public J0 = 42
//                 > ```
  }
  
