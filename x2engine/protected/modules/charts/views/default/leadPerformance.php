<?php
/* * *******************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 *
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 *
 * Company website: http://www.x2engine.com
 * Community and support website: http://www.x2community.com
 *
 * Copyright © 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this
 *   list of conditions and the following disclaimer in the documentation and/or
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be
 *   used to endorse or promote products derived from this software without
 *   specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 * ****************************************************************************** */
?>
<?php
$this->menu = array(
	array('label' => Yii::t('dashboard', 'Grid Builder'), 'url'=>array('gridReport')),
	array('label' => Yii::t('dashboard', 'Lead Performance')),
);
Yii::app()->clientScript->registerScript('leadPerformance',"
	$('#startDate,#endDate').change(function() {
		$('#dateRange').val('custom');
	});

	$('#lead-volume-chart').bind('jqplotDataHighlight', function (e, seriesIndex, pointIndex, data) {
			$('#pie-tooltip').html('<div style=\"font-size:14px;font-weight:bold;padding:5px;color:#000;background:white;background:rgba(255,255,255);\">' + data[0] + '</span>');
			$('#pie-tooltip').show();
			
			var chart_left = $('#lead-volume-chart').position().left;
				var chart_top = $('#lead-volume-chart').position().top;
		
			$(document).bind('mousemove.pieChart',function(e) {
				x = e.pageX; //plot.axes.xaxis.u2p(data[0]),  // convert x axis unita to pixels
				y = e.pageY; //plot.axes.yaxis.u2p(data[1]);  // convert y axis units to pixels
				$('#pie-tooltip').css({left:x-chart_left+5, top:y-50});
			});
		});

		// Bind a function to the unhighlight event to clean up after highlighting.
		$('#lead-volume-chart').bind('jqplotDataUnhighlight', 
		function (e, seriesIndex, pointIndex, data) {
			$(document).unbind('mousemove.pieChart');
			$('#pie-tooltip').empty();
			$('#pie-tooltip').hide();
		});

",CClientScript::POS_READY);
?>
<div class="form">
	<h2><?php echo Yii::t('dashboard', 'Lead Performance'); ?></h2>

	<?php
	$_GET = array();
	
	$form = $this->beginWidget('CActiveForm', array(
		'action'=>'leadPerformance',
		'id'=>'dateRangeForm',
		'enableAjaxValidation'=>false,
		'method'=>'get'
	));

	// $range = 30; //$model->dateRange;
	
	// echo $startDate .' '.$endDate;
	$userName = Yii::app()->user->getName();
	$filters = array(
		'createDate BETWEEN '.$dateRange['start'].' AND '.$dateRange['end']
	);
	?>
	<div class="row">
		<div class="cell">
			<?php echo CHtml::label(Yii::t('dashboard', 'Workflow'),'workflow'); ?>
			<?php
			$workflowOptions = array();
			$query = Yii::app()->db->createCommand()
			->select('id,name')
			->from('x2_workflows')->query();
			while(($row = $query->read()) !== false)
				$workflowOptions[$row['id']] = $row['name'];

			echo CHtml::dropDownList('workflow',$workflow,$workflowOptions);
			?>
		</div>
		<div class="cell">
			<?php echo CHtml::label(Yii::t('dashboard', 'Start Date'),'startDate'); ?>
			<?php
			Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
			
			$this->widget('CJuiDateTimePicker',array(
				'name'=>'start',
				// 'value'=>$startDate,
				'value'=>$this->formatDate($dateRange['start']),
				// 'title'=>Yii::t('app','Start Date'),
				// 'model'=>$model, //Model object
				// 'attribute'=>$field->fieldName, //attribute name
				'mode'=>'date', //use "time","date" or "datetime" (default)
				'options'=>array(
					'dateFormat'=>$this->formatDatePicker(),
					'changeMonth'=>true,
					'changeYear'=>true,

				), // jquery plugin options
				'htmlOptions'=>array('id'=>'startDate','width'=>20),
				'language' => (Yii::app()->language == 'en')? '':Yii::app()->getLanguage(),
			));
			?>
		</div>
		<div class="cell">
			<?php echo CHtml::label(Yii::t('dashboard', 'End Date'),'startDate'); ?>
			<?php
			$this->widget('CJuiDateTimePicker',array(
				'name'=>'end',
				'value'=>$this->formatDate($dateRange['end']),
				// 'value'=>$endDate,
				'mode'=>'date', //use "time","date" or "datetime" (default)
				'options'=>array(
					'dateFormat'=>$this->formatDatePicker(),
					'changeMonth'=>true,
					'changeYear'=>true,
				),
				'htmlOptions'=>array('id'=>'endDate','width'=>20),
				'language' => (Yii::app()->language == 'en')? '':Yii::app()->getLanguage(),
			));
			?>
		</div>
		<div class="cell">
			<?php echo CHtml::label(Yii::t('dashboard', 'Date Range'),'range'); ?>
			<?php
			echo CHtml::dropDownList('range',$dateRange['range'],array(
				'custom'=>Yii::t('dashboard','Custom'),
				'thisWeek'=>Yii::t('dashboard','This Week'),
				'thisMonth'=>Yii::t('dashboard','This Month'),
				'lastWeek'=>Yii::t('dashboard','Last Week'),
				'lastMonth'=>Yii::t('dashboard','Last Month'),
				// 'lastQuarter'=>Yii::t('dashboard','Last Quarter'),
				'thisYear'=>Yii::t('dashboard','This Year'),
				'lastYear'=>Yii::t('dashboard','Last Year'),
                                'all'=>Yii::t('dashboard','All Time'),
				
			),array('id'=>'dateRange'));
			?>
		</div>
		<div class="cell">
			<?php echo CHtml::label(Yii::t('dashboard', 'Strict Mode'),'strict'); ?>
			<?php
			echo CHtml::checkbox('strict',$dateRange['strict'],array('id'=>'strict'));
			?>
		</div>
	</div>
	<div class="row">
		<div class="cell">
                    <label>Field</label>
			<?php
				
                                $fields=Fields::model()->findAllByAttributes(array('modelName'=>'Contacts'));
                                $data=array();
                                foreach($fields as $field){
                                    $data[$field->fieldName]=$field->attributeLabel;
                                }
                                
                                echo CHtml::dropDownList('field','leadSource',$data,
                                    array(
                                        'id'=>'field-selector',
                                        'ajax' => array(
                                        'type'=>'GET', //request type
                                        'url'=>CController::createUrl('getOptions'), //url to call.
                                        //Style: CController::createUrl('currentController/methodToCall')
                                        'update'=>'#field-options', //selector to update
                                        'complete'=>'function(){
                                            $("#field-options").attr("name","Contacts["+$("#field-selector").val()+"]");
                                            $("#field-options").autocomplete( "option", "source", "getOptions?fieldType="+$("#field-selector").val());
                                         }',
                                        //'data'=>'js:"modelType="+$("'.CHtml::activeId($model,'modelType').'").val()' 
                                        //leave out the data key to pass all form values through
                                    )));
				
				$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
                                    'name'=>'Contacts['.$fieldName.']',
                                    'source'=>'getOptions?fieldType='.$fieldName,
                                    'value'=>$input,
                                    // additional javascript options for the autocomplete plugin
                                    
                                    'htmlOptions'=>array(
                                        'id'=>'field-options',
                                    ),
                                ));
			?>
		</div>
		
		<div class="cell">
			<?php echo CHtml::submitButton(Yii::t('dashboard','Go'),array('class'=>'x2-button','style'=>'margin-top:13px;')); ?>
		</div>
	</div>
		<?php
                $columns=array(
                    'user'=>array(
                            'name'=>'user',
                            'header'=>Yii::t('contacts','User'),
                            'value'=>'empty($data["id"])? $data["name"] : CHtml::link($data["name"],array("/users/".$data["id"]))',
                            'type'=>'raw',
                            'headerHtmlOptions'=>array('style'=>'width:20%')
                    ),
                    'leads'=>array(
                            'name'=>'leads',
                            'header'=>Yii::t('contacts','Contacts'),
                            'value'=>'$data["leads"]',
                            'type'=>'raw',
                    ),
                );
                if(count($stageIds)>0){
                    foreach($stageIds as $name=>$id){
                        $columns[$name]=array(
                                            'name'=>$name,
                                            'header'=>Yii::t('contacts',$name),
                                            'value'=>'$data["'.$name.'"]',
                                            'type'=>'raw',
                                        );
                    }
                }
		if(isset($dataProvider)) {
		$this->widget('zii.widgets.grid.CGridView', array(
			'id'=>'lead-activity-grid',
			'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
			'template'=> '{items}{pager}',
			// 'template'=> '<h2>'.Yii::t('dashboard','Lead Activity').'</h2><div class="title-bar">'
				// .CHtml::link(Yii::t('app','Advanced Search'),'#',array('class'=>'search-button')) . ' | '
				// .CHtml::link(Yii::t('app','Clear Filters'),array(Yii::app()->controller->action->id,'clearFilters'=>1)) . ' | '
				// .CHtml::link(Yii::t('app','Columns'),'javascript:void(0);',array('class'=>'column-selector-link'))
				// .'{summary}</div>{items}{pager}',
			'dataProvider'=>$dataProvider,
			// 'enableSorting'=>false,
			'enablePagination'=>false,
			// 'model'=>$model,
			//'filter'=>$model,
			// 'columns'=>$columns,
			//'modelName'=>'Contacts',
			// 'viewName'=>'leadpercontacts',
			'columns'=>$columns,
		));
	}
	?><br>
	<?php
	$form = $this->endWidget();

	?>
	
</div>








