INSERT INTO generated_export_stats (
	SELECT
		NULL as id,
		DATE( led.export_date ) as date,
		'Poi' as model,
		v.id as vendor_id,
		uicat.name as ui_category,
		uicat.id as ui_category_id,
		count(ui.id) as total
	FROM
		poi p,
		log_export_date led,
		vendor v,
		linking_vendor_poi_category lvpc,
		linking_vendor_poi_category_ui_category ui,
		ui_category uicat
	WHERE
		p.vendor_id = v.id AND
		p.id = lvpc.poi_id AND
		lvpc.vendor_poi_category_id = ui.vendor_poi_category_id AND
		led.model = 'Poi' AND
		p.id = led.record_id AND
		ui.ui_category_id = uicat.id
	GROUP BY
		v.id, ui.ui_category_id, DATE( led.export_date )
);