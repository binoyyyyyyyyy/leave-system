<?php

/**
 * All leave applications for one teacher, newest first.
 *
 * @return list<array<string,mixed>>
 */
function teacher_my_leaves_list(PDO $pdo, int $teacherId): array
{
    $sql = "
        SELECT
            la.id,
            la.date_filed,
            la.date_from,
            la.date_to,
            la.working_days_applied,
            la.is_half_day,
            la.status,
            la.vacation_detail,
            la.abroad_specify,
            la.sick_detail,
            la.illness_details,
            la.special_leave_women_details,
            la.study_leave_detail,
            la.commutation,
            la.admin_remarks,
            la.rejected_reason,
            la.approved_at,
            la.rejected_at,
            lt.leave_name
        FROM leave_applications la
        INNER JOIN leave_types lt ON la.leave_type_id = lt.id
        WHERE la.teacher_id = ?
        ORDER BY la.date_filed DESC, la.id DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$teacherId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
