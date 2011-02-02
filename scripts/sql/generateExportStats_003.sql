INSERT INTO generated_export_stats (
	SELECT
		NULL as id,
		DATE( led.export_date ) as date,
		'Event' as model,
		v.id as vendor_id,
		uicat.name as ui_category,
		uicat.id as ui_category_id,
		count(ui.id) as total
	FROM
		event e,
		log_export_date led,
		vendor v,
		linking_vendor_event_category lvec,
		linking_vendor_event_category_ui_category ui,
		ui_category uicat
	WHERE
		e.vendor_id = v.id AND
		e.id = lvec.event_id AND
		lvec.vendor_event_category_id = ui.vendor_event_category_id AND
		led.model = 'Event' AND
		e.id = led.record_id AND
		ui.ui_category_id = uicat.id
	GROUP BY
		v.id, ui.ui_category_id, DATE( led.export_date )
);