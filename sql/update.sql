ALTER TABLE llx_doliletter_envelope ADD fk_project integer;
ALTER TABLE llx_doliletter_envelope_signature ADD ip varchar(255);

-- L'inscription libre a la diffusion est desormais active par defaut. Les installations creees
-- avant ce changement ont recu la constante a 0 a leur activation, et insert_const() ne retouche
-- jamais une constante existante : cette bascule est le seul moyen de les aligner.
-- Elle ne joue qu'une fois, tracee par sa propre constante, pour ne pas rallumer l'option a chaque
-- reactivation du module chez un administrateur qui l'a volontairement coupee.
UPDATE llx_const c
  JOIN (SELECT COUNT(*) AS done FROM llx_const WHERE name = 'DOLILETTER_SPREAD_PUBLIC_REGISTER_DEFAULTED') g
   SET c.value = '1'
 WHERE c.name = 'DOLILETTER_SPREAD_PUBLIC_REGISTER' AND g.done = 0;

INSERT IGNORE INTO llx_const (name, type, value, note, visible, entity)
     SELECT 'DOLILETTER_SPREAD_PUBLIC_REGISTER_DEFAULTED', 'integer', '1', NULL, 0, entity
       FROM llx_const WHERE name = 'DOLILETTER_SPREAD_PUBLIC_REGISTER';
