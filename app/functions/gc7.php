<?php
/**
 * Var_dump avec &lt;pre>
 *
 * @param      $arr
 * @param null $var
 */
function vd( $arr, $var = null )
{

	echo '<small>Lg. ' . debug_backtrace()[ 0 ][ 'line' ] . ' - ' . debug_backtrace()[ 0 ][ 'file' ] . '</small>';
	echo '<pre>' . ( ( null === $var ) ? '' : $var . ':' );
	var_dump( $arr );
	echo '</pre>';
}

/**
 * Var_dump compressé d'une collection pour le champs nom
 *
 * @param      $arr
 * @param null $var
 */
function vdColl( $arr, $var = null )
{
	echo getDebugBagtrace() . '<ul class="vdColl"> ' . ( ( null === $var ) ? '' : '<span>' . $var . ':</span><br>' );
	foreach ( $arr as $k => $m ) {
		echo $k . ': ' . $m->nom . '<br>';
	}

	echo '</ul>';
}

function getDebugBagtrace()
{
	return '<small>Lg. ' . debug_backtrace()[ 1 ][ 'line' ] . ' - ' . debug_backtrace()[ 1 ][ 'file' ] . '</small>';
}
