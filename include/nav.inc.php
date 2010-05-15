<ul>
<li>
	<a href="./<?php echo ($group == "" || $group == "[none]" || $is_fix_group ? "" : "?group=".$group); ?>"><?php echo msg('HOME'); ?></a>
</li>
<?php if(! $read_only) { ?>
<li class="all">
	<a href="edit<?php echo $page_ext; ?>"><?php echo msg('ADD_NEW'); ?></a>
</li>
<?php 
} 
if(!$read_only && $public_group_edit && $table_groups != "" && !$is_fix_group)
{ ?>
	<li class="admin">
		<a href="group<?php echo $page_ext; ?>"><?php echo msg('GROUPS'); ?></a>
	</li>
<?php } ?>
	<li class="all">
		<a href="birthdays<?php echo $page_ext; ?>"><?php echo msg('NEXT_BIRTHDAYS'); ?></a>
	</li>
	<li class="export">
    	<a href="view<?php echo $page_ext_qry; ?>all&amp;print"><?php echo msg('PRINT_ALL'); ?></a>
  </li>
	<li class="export">
      <a href="view<?php echo $page_ext_qry; ?>all&amp;print&amp;phones"><?php echo msg('PRINT_PHONES'); ?></a>
  </li>
	<li class="export">
    	<a href="csv<?php echo $page_ext; ?>"><?php echo msg('EXPORT_CSV'); ?></a>
  </li> 
<?php if(!$read_only) { ?>
	<li class="import">
    	<a href="ldif<?php echo $page_ext; ?>"><?php echo msg('IMPORT'); ?></a>
  </li> 
<?php } ?>
</ul>
