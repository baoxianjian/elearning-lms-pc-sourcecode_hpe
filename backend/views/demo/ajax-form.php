<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 3/8/15
 * Time: 2:22 PM
 */

?>

<head>



    <head>
        <script type="text/javascript" src="/vendor/bower/jquery/dist/jquery.min.js"></script>
        <script type="text/javascript">
            $(document).ready(function(){

                var url = 'http://elearninglmsyii/backend/tree-type/test.html';

//                $("form").submit(function() {
//
//                    $.ajax({
//                        type: "POST",
//                        url: url,
//                        data: null, // serializes the form's elements.
//                        success: function(data)
//                        {
//                            alert(data); // show response from the php script.
//                        }
//                    });
//
//                    return false; // avoid to execute the actual submit of the form.
//                });

                alert($("form").attr("action"));
                $("form").submit(function() {
                    var url = 'http://elearninglmsyii/backend/tree-type/test.html';
                    $.ajax({
                        url: url,
                        cache: true,
                        type: "get",
                        dataType: 'json',
                        data: null,
                        async: false,
                        success: function(data)
                        {
                            alert(data.result); // show response from the php script.
                        }
                    });
                    return false;
                });
            });

            function test()
            {
//                alert('1');
                $("form").submit();
            }


        </script>
    </head>
    <body>
    <form name="input" action="http://elearninglmsyii/backend/tree-type/test.html" method="get" id="test">

<!--        <input type="submit" value="Submit">-->
    </form>

    <a href="#" onclick="test();" >a</>
    </body>
<html>