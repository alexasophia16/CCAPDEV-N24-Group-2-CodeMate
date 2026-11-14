<?php
class TutorVerification extends Model
{
    public function pending(): array {
        return $this->fetchAll(
            'SELECT u.user_id, u.full_name, u.email, tp.experience, tp.bio,
                    tp.verification_status, tp.applied_at,
                    GROUP_CONCAT(DISTINCT c.course_code ORDER BY c.course_code SEPARATOR ", ") AS subjects
             FROM tutor_profiles tp
             JOIN users u ON u.user_id = tp.tutor_id
             LEFT JOIN tutor_courses tc ON tc.tutor_id = tp.tutor_id
             LEFT JOIN courses c ON c.course_id = tc.course_id
             WHERE tp.verification_status = "pending"
             GROUP BY u.user_id, u.full_name, u.email, tp.experience, tp.bio, tp.verification_status, tp.applied_at
             ORDER BY tp.applied_at ASC'
        );
    }
    public function review(int $tutorId, string $decision): bool {
        if (!in_array($decision, ['verified', 'rejected'], true)) return false;
        return $this->execute(
            'UPDATE tutor_profiles SET verification_status = ?, reviewed_at = NOW()
             WHERE tutor_id = ? AND verification_status = "pending"',
            [$decision, $tutorId]
        ) > 0;
    }
}
