<script src="https://gist.github.com/<?= str_replace([':', ' '], '/', trim($section->slug)) ?>.js"></script>
<?php
return [
	'name' => 'Gist',
	'props' => [
		'slug' => [
			'type' => 'string',
			'required' => true
		]
	]
];
