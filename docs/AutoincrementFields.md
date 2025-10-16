# Autoincrement Fields

## Configuration and conventions
Each autoincrement table should have before insert trigger for each row,
that use sequence with name `SEQ_{TABLENAME}`.
Don't escape sequence names.

## Schema shell support
In case of using schema CakePHP will support and create automatically
sequences and triggers for autoincrement fields.

## Example
For example for table posts we should have sequence `SEQ_posts`
and trigger

```sql
CREATE TRIGGER T_POSTS
   BEFORE INSERT
   ON "posts"
   FOR EACH ROW
DECLARE
   last_Sequence NUMBER;
   last_InsertID NUMBER;
BEGIN
   SELECT SEQ_POSTS.NEXTVAL INTO :NEW."id" FROM DUAL;
   IF (:NEW."id" IS NULL OR :NEW."id" = 0) THEN
      SELECT SEQ_POSTS.NEXTVAL INTO :NEW."id" FROM DUAL;
   ELSE
      SELECT NVL(Last_Number, 0) INTO last_Sequence
        FROM User_Sequences
       WHERE Sequence_Name = 'SEQ_POSTS';
      SELECT :NEW."id" INTO last_InsertID FROM DUAL;
      WHILE (last_InsertID > last_Sequence) LOOP
         SELECT SEQ_POSTS.NEXTVAL INTO last_Sequence FROM DUAL;
      END LOOP;
   END IF;
END;
```