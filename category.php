<? /*Подключение Header*/ ?>

<form action="#" method="get">
	<div class="filter">
		<? 
		$cat = $GLOBALS['cat']; 
		$filter_group = array('brand', 'color', 'surface');
        $order = array('price' => 'asc');
        if(isset($_GET['order']) && isset($_GET['orderdir'])) {
            $order = array($_GET['order'] => $_GET['orderdir']);
        }
		$args = array(
			'cat'		=> $cat,
            'meta_key' => reset(array_keys($order)),
            'post_type' => 'any',
			'orderby' 	=> 'meta_value_num',
            'order' => reset($order),
			'meta_query' => array( 
				'relation'=>'AND',
			)
		);
        if(reset(array_keys($order)) == 'date') {
            unset($args['meta_key']);
            $args['orderby'] = 'date';
        }
            
		?>
		<?foreach($filter_group as $group):?>
			<?$test = get_field_object($group)?>
			<?
			if( isset( $_GET[$test['name']] ) && $_GET[$test['name']] ) {
                if( is_array( $_GET[$test['name']] ) ) {
                    $multi_filter = [
                        'relation'=>'OR'
                    ];
                    foreach($_GET[$test['name']] as $value)
                    $multi_filter[] = array(
                        'key' => $test['name'],
                        'value' => $value,
                        'compare' => 'LIKE'
                    );
                    $args['meta_query'][] = $multi_filter;
                }
                else 
				$args['meta_query'][] = array(
					'key' => $test['name'],
					'value' => $_GET[$test['name']],
                    'compare' => '='
				);
            }
			?>
			<?if($test['choices']):?>
				<div class="filter__row">
					<select data-placeholder="- <?=$test['label']?> -" <?=$test['type']=='checkbox'?'multiple':''?> 
                            name="<?=$test['name']?><?php if($test['type']=='checkbox') : ?>[]<?php endif; ?>" 
                            <?php if($test['type']=='checkbox') : ?>class="multiple ignore"<?php endif; ?>>
						<option></option>
						<?foreach($test['choices'] as $keys => $values):?>
							<option value="<?=$keys?>"><?=$values?></option>
						<?endforeach?>
					</select>
				</div>
			<?endif?>

		<?endforeach;?>
	</div>
	<div class="sort">
		<a href="#" class="sort__link <?=(reset(array_keys($order)) == 'date' ? 'active' : '')?>">
            <label for="sort_new" data-order="desc">
                По новизне
                <span class="sort__link__arrow <?=reset($order)=='desc'?'sort__link__arrow__down':''?>"></span>
            </label>
        </a>
		<a href="#" class="sort__link <?=(reset(array_keys($order)) == 'views' ? 'active' : '')?>">
            <label for="sort_popular" data-order="desc">
                По популярности
                <span class="sort__link__arrow <?=reset($order)=='desc'?'sort__link__arrow__down':''?>"></span>
            </label>
        </a>
		<a href="#" class="sort__link  <?=(reset(array_keys($order)) == 'price' ? 'active' : '')?>">
            <label for="sort_price" data-order="asc">По цене
                <span class="sort__link__arrow <?=reset($order)=='desc'?'sort__link__arrow__down':''?>"></span>
            </label>
        </a>
        <div style="display: none;">
            <input type="radio" name="order" value="date" id="sort_new" <?=(reset(array_keys($order)) == 'date' ? 'checked' : '')?> class="ignore"/>
            <input type="radio" name="order" value="views" id="sort_popular" <?=(reset(array_keys($order)) == 'views' ? 'checked' : '')?> class="ignore"/>
            <input type="radio" name="order" value="price" id="sort_price" <?=(reset(array_keys($order)) == 'price' ? 'checked' : '')?> class="ignore"/>
            <input type="hidden" name="orderdir" value="<?=reset($order)?>" id="js-orderdir">
        </div>
	</div>
	<input class="filter__btn" type="submit" value="Подобрать">
</form>

<!-- Выводим посты -->

<div id="pjax-container"><!-- Обязательно добавляем id="pjax-container" -->
    <? 
    query_posts($args); //Передаем $args, который формируется на основе выбранных значений
    if ( have_posts() ) : while ( have_posts() ) : the_post();?>
        
        <!-- ... Выводим посты как душе угодно ... -->

    <?endwhile; endif;?>
    <? wp_reset_query();?>
</div>

<!-- JavaScript - лучше вынести в отдельный файл -->

<script>
    if($("select.multiple").length)
        $("select.multiple").select2({});
    
    var prevOrder = $('input[name="order"]:checked').val();
    
    $('body').on('click', '.sort__link label', function(e){
        e.preventDefault();
        var dir = 'desc';
        if($('input[name="order"]:checked').val() != prevOrder) {
            dir = $(this).data('order');
//            console.log('dir', dir);
            $(this).parent().addClass('active').siblings().removeClass('active');
        }
        else {
            //  меняем порядок сортировки
            dir = $('#js-orderdir').val() == 'asc' ? 'desc' : 'asc';
        }
        $('#js-orderdir').val(dir);
        var arrow = $('.sort__link.active').find('.sort__link__arrow');
        if(dir == 'asc') 
            arrow.removeClass('sort__link__arrow__down');
        else if(!arrow.hasClass('sort__link__arrow__down')) 
            arrow.addClass('sort__link__arrow__down');
        prevOrder = $('input[name="order"]:checked').val();
    });
    
    if($('.filter__btn').length) 
    {
        $('.filter__btn').click(function(e){
            e.preventDefault();
            var form = $('.filter-block form');
            $.pjax({
                container: "#pjax-container", 
                fragment: "#pjax-container", 
                timeout: 2000,
                url: window.location.href,
                data: form.serialize(),
                push: false,
                scrollTo: false
            });
        
            return false; // <-- cancel the default event            
        });
    }
</script>

<? /*Подключение Footer*/ ?>