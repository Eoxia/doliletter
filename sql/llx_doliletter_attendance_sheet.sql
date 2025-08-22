create table llx_doliletter_attendance_sheet (
    rowid int(11) NOT NULL AUTO_INCREMENT,
    ref varchar(255) NOT NULL,
    ref_ext varchar(255) DEFAULT NULL,
    entity int(11) NOT NULL DEFAULT 1,
    date_creation datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    tms timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    note_public text DEFAULT NULL,
    note_private text DEFAULT NULL,
    status int(11) NOT NULL DEFAULT 0,
    object_type varchar(255) NOT NULL,
    fk_object int(11) DEFAULT NULL,
    fk_user_creat int(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (rowid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;