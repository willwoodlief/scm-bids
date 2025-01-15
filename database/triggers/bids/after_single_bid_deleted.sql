CREATE TRIGGER after_single_bid_deleted
    AFTER DELETE ON scm_plugin_bid_singles
    FOR EACH ROW
BEGIN

    # remove applied permissions
    DELETE a FROM user_role_applies a WHERE a.per_unit_of = 'bid' AND a.per_unit_id = OLD.id;

END
