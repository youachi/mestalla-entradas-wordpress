<?php
/*
Plugin Name: Mestalla Entradas
Description: Acceso de aficionado y reservas locales de entradas para Mestalla.
Version: 1.0.0
Author: Mestalla Entradas
*/
if (!defined('ABSPATH')) exit;

function mestalla_events(){return [
 'real-madrid'=>['Real Madrid','RMA','Sáb · 17 oct','21:00','Desde 55 €','Jornada 9'],
 'sevilla'=>['Sevilla FC','SEV','Dom · 1 nov','18:30','Desde 32 €','Jornada 11'],
 'villarreal'=>['Villarreal CF','VIL','Sáb · 21 nov','16:15','Desde 28 €','Jornada 13'],
];}
function mestalla_matches_shortcode(){
 $out=''; foreach(mestalla_events() as $slug=>$e){$out.='<article class="match"><div class="match-top"><span>'.esc_html($e[5]).'</span><b>◆ Mestalla</b></div><div class="match-main"><div class="match-teams"><div class="club"><span class="club-mark">VCF</span><span>VALENCIA CF</span></div><span class="vs">VS</span><div class="club"><span class="club-mark opponent">'.esc_html($e[1]).'</span><span>'.esc_html($e[0]).'</span></div></div><div class="match-meta"><span>'.esc_html($e[2]).'</span><strong>'.esc_html($e[3]).'</strong></div></div><div class="match-footer"><span class="price">'.esc_html($e[4]).'</span><a class="btn" href="'.esc_url(home_url('/entradas/?partido='.$slug)).'">Elegir sitio&nbsp; ↗</a></div></article>';}
 return $out;
}
add_shortcode('mestalla_matches','mestalla_matches_shortcode');

function mestalla_reservation_submit(){
 if(!is_user_logged_in()){wp_safe_redirect(home_url('/acceso/?reserva=login'));exit;}
 if(empty($_POST['mestalla_nonce'])||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['mestalla_nonce'])),'mestalla_reserve'))wp_die('La solicitud ha caducado. Vuelve a intentarlo.');
 $events=mestalla_events();$slug=isset($_POST['partido'])?sanitize_key(wp_unslash($_POST['partido'])):'';$qty=isset($_POST['entradas'])?absint($_POST['entradas']):0;
 if(!isset($events[$slug])||$qty<1||$qty>6){wp_safe_redirect(home_url('/entradas/?estado=error'));exit;}
 $id=wp_insert_post(['post_type'=>'mestalla_reserva','post_status'=>'private','post_title'=>'Reserva — '.wp_get_current_user()->display_name.' — '.$events[$slug][0],'post_author'=>get_current_user_id()]);
 if($id&&!is_wp_error($id)){update_post_meta($id,'_mestalla_partido',$slug);update_post_meta($id,'_mestalla_cantidad',$qty);update_post_meta($id,'_mestalla_estado','Pendiente');}
 wp_safe_redirect(home_url('/mi-cuenta/?reserva=ok'));exit;
}
add_action('admin_post_mestalla_reserve','mestalla_reservation_submit');
add_action('admin_post_nopriv_mestalla_reserve','mestalla_reservation_submit');

function mestalla_booking_shortcode(){
 if(isset($_GET['estado'])&&$_GET['estado']==='error')$notice='<div class="notice error">Revisa el partido y el número de entradas (máximo 6).</div>';else $notice='';
 if(!is_user_logged_in())return $notice.do_shortcode('[mestalla_login]');
 $selected=isset($_GET['partido'])?sanitize_key(wp_unslash($_GET['partido'])):'';
 $out=$notice.'<div class="card-panel"><div class="kicker">Compra local · Mestalla</div><h2>Elige tu partido</h2><p>Rellena la solicitud y la verás en tu perfil de aficionado.</p><form method="post" action="'.esc_url(admin_url('admin-post.php')).'"><input type="hidden" name="action" value="mestalla_reserve">'.wp_nonce_field('mestalla_reserve','mestalla_nonce',true,false).'<div class="form-grid"><div class="full"><label for="partido">Partido</label><select required id="partido" name="partido"><option value="">Selecciona un encuentro</option>';
 foreach(mestalla_events() as $slug=>$e)$out.='<option value="'.esc_attr($slug).'" '.selected($selected,$slug,false).'>Valencia CF — '.esc_html($e[0]).' · '.esc_html($e[2]).' · '.esc_html($e[4]).'</option>';
 $out.='</select></div><div><label for="entradas">Número de entradas</label><select id="entradas" name="entradas"><option>1</option><option>2</option><option>3</option><option>4</option><option>5</option><option>6</option></select></div><div><label>Cuenta</label><input value="'.esc_attr(wp_get_current_user()->display_name).'" disabled></div><div class="full"><button class="btn" type="submit">Solicitar entradas&nbsp; ↗</button><p style="font-size:11px;color:#78808c">Demo local: la solicitud se guarda en WordPress para su gestión. No realiza pagos.</p></div></div></form></div>';
 return $out;
}
add_shortcode('mestalla_booking','mestalla_booking_shortcode');

function mestalla_login_shortcode(){
 if(is_user_logged_in())return '<div class="notice">Has iniciado sesión como <strong>'.esc_html(wp_get_current_user()->display_name).'</strong>. <a href="'.esc_url(home_url('/mi-cuenta/')).'">Ir a mi cuenta →</a></div>';
 ob_start();wp_login_form(['echo'=>true,'redirect'=>home_url('/mi-cuenta/'),'remember'=>true,'label_username'=>'Usuario','label_password'=>'Contraseña','label_remember'=>'Recuérdame','label_log_in'=>'Entrar en Mestalla']);return '<div class="card-panel"><div class="kicker">Acceso de aficionado</div><h2>Bienvenido a casa.</h2><p>Entra para consultar tus reservas o reservar tu sitio en la grada.</p>'.ob_get_clean().'<p style="font-size:12px;color:#78808c">¿No tienes acceso? Usa una de las cuentas locales de demostración configuradas para este sitio.</p></div>';
}
add_shortcode('mestalla_login','mestalla_login_shortcode');

function mestalla_account_shortcode(){
 if(!is_user_logged_in())return do_shortcode('[mestalla_login]');
 $user=wp_get_current_user();$items=get_posts(['post_type'=>'mestalla_reserva','post_status'=>'private','author'=>$user->ID,'numberposts'=>20]);$out='<div class="card-panel"><div class="kicker">Tu espacio</div><h2>Hola, '.esc_html($user->display_name).'.</h2><p>Este es tu rincón de Mestalla. Aquí tienes tus solicitudes de entradas.</p><a class="btn" href="'.esc_url(home_url('/entradas/')).'">Nueva reserva&nbsp; ↗</a><h3 style="margin-top:32px">Mis reservas</h3>';
 if(!$items)$out.='<div class="notice">Todavía no tienes reservas. El próximo partido te espera.</div>';
 foreach($items as $item){$slug=get_post_meta($item->ID,'_mestalla_partido',true);$e=mestalla_events()[$slug]??null;$qty=(int)get_post_meta($item->ID,'_mestalla_cantidad',true);$status=get_post_meta($item->ID,'_mestalla_estado',true);$out.='<div class="reservation"><div><strong>'.esc_html($e?$e[0]:'Partido').'</strong><small>'.esc_html($e?$e[2]:'').' · '.$qty.' entrada(s)</small></div><span class="notice" style="margin:0;padding:8px 12px">'.esc_html($status).'</span></div>';}
 $out.='<p><a href="'.esc_url(wp_logout_url(home_url('/'))).'">Cerrar sesión</a></p></div>';return $out;
}
add_shortcode('mestalla_account','mestalla_account_shortcode');

function mestalla_register_reservations(){register_post_type('mestalla_reserva',['labels'=>['name'=>'Reservas Mestalla','singular_name'=>'Reserva','menu_name'=>'Reservas de entradas'],'public'=>false,'show_ui'=>true,'show_in_menu'=>true,'supports'=>['title','author'],'capability_type'=>'post','map_meta_cap'=>true,'menu_icon'=>'dashicons-tickets-alt']);}
add_action('init','mestalla_register_reservations');

function mestalla_configure_profiles(){
 global $wp_roles;if(!$wp_roles)$wp_roles=wp_roles();
 if(isset($wp_roles->roles['administrator']))$wp_roles->roles['administrator']['name']='Administrador del sitio';
 if(isset($wp_roles->roles['editor']))$wp_roles->roles['editor']['name']='Gestor de entradas';
 if(isset($wp_roles->roles['subscriber']))$wp_roles->roles['subscriber']['name']='Aficionado';
 update_option($wp_roles->role_key,$wp_roles->roles);
}
register_activation_hook(__FILE__,'mestalla_configure_profiles');
