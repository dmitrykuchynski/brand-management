<?php

class Brand_Management_Storage {
	public static array $options = [];
	public static array $campaign_tables = [];

	public static function set_option( $key, $value ): array {
		self::$options[ $key ] = $value;

		return self::$options;
	}

	public static function set_campaign( $id, $data ): array {
		self::$campaign_tables[ $id ] = $data;

		return self::$campaign_tables;
	}
}
