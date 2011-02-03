INSERT INTO generated_export_stats (
	SELECT
		NULL as id,
		DATE( led.export_date ) as date,
		'Movie' as model,
		v.id as vendor_id,
		'Film' as ui_category,
		1 as ui_category_id,
		count( * ) as total
	FROM
		movie m,
		log_export_date led,
		vendor v
	WHERE
		m.vendor_id = v.id AND
		led.model = 'Movie' AND
		m.id = led.record_id
	GROUP BY
		v.id, ui_category, DATE( led.export_date )
);