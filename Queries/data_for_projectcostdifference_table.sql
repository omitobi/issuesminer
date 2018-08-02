SELECT
  t1.ProjectId,
  t1.ModuleLevel,
  t1.ModulePath,
  t2.ModulePath                                AS ModulePath2,
  t1.AlternativeCost,
  t2.AlternativeCost                           as AlternativeCost2,
  ABS(t1.AlternativeCost - t2.AlternativeCost) AS costDifference,
  CASE WHEN (ABS(t1.AlternativeCost - t2.AlternativeCost) > 8000) #threshold of 95% ==> 8000 LOC
    THEN IF(t1.AlternativeCost > t2.AlternativeCost, 1, -1)
  ELSE 0 END                                   as costCompare,
  ABS(t1.fixes - t2.fixes)                     AS fixesDifference,
  CASE WHEN (ABS(t1.fixes - t2.fixes) > 4) #using 4 at 90% threshold
    THEN IF(t1.fixes > t2.fixes, 1, -1)
  ELSE 0 END                                   as fixesCompare,
  t1.loc                                       AS loc1,
  t2.loc                                       AS loc2,
  t1.fixes,
  t2.fixes                                     as fixes2,
  t1.Date
FROM projectmoduleachurnhistory AS t1 INNER JOIN projectmoduleachurnhistory AS t2
    ON t1.Date = t2.Date
       AND t1.ProjectId = t2.ProjectId
       AND t1.ModulePath <> t2.ModulePath
       AND t1.ModulePath NOT LIKE CONCAT(t2.ModulePath, '%')
       AND t2.ModulePath NOT LIKE CONCAT(t1.ModulePath, '%')
WHERE t1.ProjectId = 9
      AND t1.ModuleLevel = 4;