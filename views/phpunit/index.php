<?php defined('SYSPATH') or die('No direct script access.') ?>

<div id="select">
	<h1>Itim Unit Tests</h1>
	<div id="groups">
		<fieldset class="tests">
			<legend>Run Tests</legend>
			<?php echo Form::open($run_uri, array('method' => 'GET'));?>

			<?php echo Form::label('run_group', __('Run a test group')); ?>
			<?php echo Form::select('group', $groups, NULL, array('id' => 'run_group'));?>

			

			<?php echo Form::submit('submit', 'Run');?>
			<?php echo Form::close();?>
		</fieldset>
		
			
		</fieldset>
	</div>
	
	<h2>Useful links</h2>
	<ul>
		<li><a href="http://www.phpunit.de/manual/current/en/">PHPUnit Manual</a></li>
		<li><a href="http://github.com/kohana/unittest">Module README</a></li>
	</ul>
</div>