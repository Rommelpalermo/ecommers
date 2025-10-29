-- Update product prices to Philippine Peso
UPDATE products SET 
    price = CASE
        WHEN id = 1 THEN 35000.00  -- Smartphone Pro
        WHEN id = 2 THEN 65000.00  -- Laptop Ultra
        WHEN id = 3 THEN 999.00    -- T-Shirt Basic
        WHEN id = 4 THEN 1999.00   -- Programming Book
        ELSE price
    END
WHERE id IN (1, 2, 3, 4);