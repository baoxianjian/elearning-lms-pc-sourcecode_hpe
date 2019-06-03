<?php
use components\widgets\TKindEditor;

?>

    <div class="module">
        <h3>Default example</h3>

        <form>
            <textarea id="content" name="content" style="width:988px;height:200px;visibility:hidden;"></textarea>
        </form>
    </div>

<?TKindEditor::widget(['input' => '#content','editorId'=>'editor']);

?>