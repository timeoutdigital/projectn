DELETE FROM log_export_error
WHERE DATE(created_at) > CURRENT_DATE - INTERVAL 6 MONTH;