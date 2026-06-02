-- Migration: add parent comment support
ALTER TABLE commentaire
  ADD COLUMN idParent VARCHAR(20) NULL AFTER idMemoire;

ALTER TABLE commentaire
  ADD CONSTRAINT fk_comment_parent
    FOREIGN KEY (idParent) REFERENCES commentaire(idCommentaire)
    ON DELETE CASCADE;

-- Optional: backfill existing comments with NULL parent (no-op)
UPDATE commentaire SET idParent = NULL WHERE idParent = '' OR idParent IS NULL;
