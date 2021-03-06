-- Copyright (C) 2021 EOXIA <dev@eoxia.com>
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see https://www.gnu.org/licenses/.

ALTER TABLE llx_doliletter_email_sending ADD INDEX idx_doliletter_email_sending_rowid (rowid);
ALTER TABLE llx_doliletter_email_sending ADD CONSTRAINT llx_doliletter_email_sending_fk_envelope FOREIGN KEY (fk_envelope) REFERENCES llx_doliletter_envelope(rowid);
ALTER TABLE llx_doliletter_email_sending ADD CONSTRAINT llx_doliletter_email_sending_fk_socpeople FOREIGN KEY (fk_socpeople) REFERENCES llx_socpeople(rowid);
