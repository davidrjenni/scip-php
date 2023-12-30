  <?php
  
  declare(strict_types=1);
  
  use TestData\ClassA;
//    ^^^^^^^^^^^^^^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassA#
  
  function fun2(): ClassA
//         ^^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 fun2().
//         documentation
//         > ```php
//         > function fun2(): \TestData\ClassA
//         > ```
//                 ^^^^^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassA#
  {
      return new ClassA();
//               ^^^^^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassA#
  }
  
