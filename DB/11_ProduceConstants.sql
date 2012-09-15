SELECT CONCAT('const ',sStringKey ,' = ',KeyID,';') as sRow FROM T_Key
WHERE sStringKey IS NOT NULL
ORDER By sStringKey;
