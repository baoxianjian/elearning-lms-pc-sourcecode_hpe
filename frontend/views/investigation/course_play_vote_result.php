<?php
?>


<a  onclick="closeFun()" class="btn btn-sm pull-right cancelBtn"><?= Yii::t('common', 'close') ?></a>
                          <div class="Result_noName_vote">
                            <h4><?=$results['answer_type'] ?><?= Yii::t('frontend', 'result') ?></h4>
                            <hr>
                            
                            <? foreach ($results['options'] as $opt): ?>
                            <div class="row">
                              <div class="col-md-12 col-sm-12">
                                <div class="form-group form-group-sm">
                                  <label class="col-sm-12 control-label"><?=$opt['option_title'] ?></label>
                                  <div class="col-sm-12">
                                    <div class="col-sm-8 voteBack"><span class="voteValue" style="width:<?=$opt['submit_num_rate'] ?>%;"></span></div>
                                    <div class="col-sm-4 voteNum"><?=$opt['submit_num'] ?>(<?=$opt['submit_num_rate'] ?>%)</div>
                                  </div>
                                </div>
                              </div>
                            </div>
                           <? endforeach; ?>
                           
                           
                          
                          </div>
                          
 <script>

  function closeFun( ){

	  $('#course_play_vote_result_id').addClass('hide');
	  $('#course_play_vote_result_id').empty();
	  }

  </script>                                      