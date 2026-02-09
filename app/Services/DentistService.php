<?php

namespace App\Services;

use App\Models\Dentist;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Dentist Availability Management Service
 * 
 * Centralizes all dentist state management including availability status.
 * Ensures dentist availability is properly synchronized with appointment workflow.
 * 
 * Dentist States:
 * - AVAILABLE: Ready for next patient
 * - BUSY: Currently treating a patient
 * - BREAK: Optional, on break (implementation optional)
 * 
 * Usage:
 * - Call setBusy() when treatment starts (IN_TREATMENT state)
 * - Call setAvailable() when treatment completes (COMPLETED state)
 * - Check isAvailable() before assigning next patient
 */
class DentistService
{
    // NOTE: Dentist.status is a BOOLEAN column (true=available, false=busy)
    // These constants are for code readability only, actual values are booleans
    const STATE_AVAILABLE = true;   // 1 in database
    const STATE_BUSY = false;       // 0 in database
    const STATE_BREAK = false;      // Not used yet, same as busy for now

    /**
     * Mark dentist as AVAILABLE (ready for next patient)
     * 
     * @param Dentist $dentist
     * @return bool
     */
    public function setAvailable(Dentist $dentist): bool
    {
        try {
            $previousState = $dentist->status;
            
            // Update status to true (available)
            $dentist->update([
                'status' => true,  // true = available
            ]);

            Log::info('Dentist marked available', [
                'dentist_id' => $dentist->id,
                'dentist_name' => $dentist->name,
                'previous_state' => $previousState,
                'new_state' => true,
            ]);

            // Log activity
            ActivityLogger::log(
                action: 'dentist_state_change',
                modelType: 'Dentist',
                modelId: $dentist->id,
                description: "Dentist {$dentist->name} marked as AVAILABLE",
                newValues: [
                    'dentist_id' => $dentist->id,
                    'dentist_name' => $dentist->name,
                    'previous_state' => $previousState,
                    'new_state' => true,
                ]
            );

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to mark dentist available', [
                'dentist_id' => $dentist->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Mark dentist as BUSY (treating patient)
     * 
     * @param Dentist $dentist
     * @return bool
     */
    public function setBusy(Dentist $dentist): bool
    {
        try {
            $previousState = $dentist->status;
            
            // Update status to false (busy)
            $dentist->update([
                'status' => false,  // false = busy
            ]);

            Log::info('Dentist marked busy', [
                'dentist_id' => $dentist->id,
                'dentist_name' => $dentist->name,
                'previous_state' => $previousState,
                'new_state' => false,
            ]);

            // Log activity
            ActivityLogger::log(
                action: 'dentist_state_change',
                modelType: 'Dentist',
                modelId: $dentist->id,
                description: "Dentist {$dentist->name} marked as BUSY",
                newValues: [
                    'dentist_id' => $dentist->id,
                    'dentist_name' => $dentist->name,
                    'previous_state' => $previousState,
                    'new_state' => false,
                ]
            );

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to mark dentist busy', [
                'dentist_id' => $dentist->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Mark dentist as on BREAK
     * 
     * Optional state for dentists taking a break.
     * During break, dentists cannot be assigned new patients.
     * 
     * @param Dentist $dentist
     * @return bool
     */
    public function setBreak(Dentist $dentist): bool
    {
        try {
            $previousState = $dentist->status;
            
            $dentist->update([
                'status' => self::STATE_BREAK,
            ]);

            Log::info('Dentist marked on break', [
                'dentist_id' => $dentist->id,
                'dentist_name' => $dentist->name,
                'previous_state' => $previousState,
                'new_state' => self::STATE_BREAK,
            ]);

            // Log activity
            ActivityLogger::log(
                action: 'dentist_state_change',
                modelType: 'Dentist',
                modelId: $dentist->id,
                description: "Dentist {$dentist->name} marked as on BREAK",
                newValues: [
                    'dentist_id' => $dentist->id,
                    'dentist_name' => $dentist->name,
                    'previous_state' => $previousState,
                    'new_state' => self::STATE_BREAK,
                ]
            );

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to mark dentist on break', [
                'dentist_id' => $dentist->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Check if dentist is AVAILABLE for next patient
     * 
     * @param Dentist $dentist
     * @return bool
     */
    public function isAvailable(Dentist $dentist): bool
    {
        return $dentist->status === self::STATE_AVAILABLE;
    }

    /**
     * Check if dentist is BUSY with patient
     * 
     * @param Dentist $dentist
     * @return bool
     */
    public function isBusy(Dentist $dentist): bool
    {
        return $dentist->status === self::STATE_BUSY;
    }

    /**
     * Check if dentist is on BREAK
     * 
     * @param Dentist $dentist
     * @return bool
     */
    public function isOnBreak(Dentist $dentist): bool
    {
        return $dentist->status === self::STATE_BREAK;
    }

    /**
     * Get all available dentists (ready for next patient)
     * 
     * @return Collection|Dentist[]
     */
    public function getAvailableDentists(): Collection
    {
        return Dentist::where('status', self::STATE_AVAILABLE)
            ->where('is_active', true)
            ->get();
    }

    /**
     * Get all busy dentists (treating patient)
     * 
     * @return Collection|Dentist[]
     */
    public function getBusyDentists(): Collection
    {
        return Dentist::where('status', self::STATE_BUSY)
            ->where('is_active', true)
            ->get();
    }

    /**
     * Get all working dentists (AVAILABLE + BUSY)
     * 
     * @return Collection|Dentist[]
     */
    public function getWorkingDentists(): Collection
    {
        return Dentist::whereIn('status', [self::STATE_AVAILABLE, self::STATE_BUSY])
            ->where('is_active', true)
            ->get();
    }

    /**
     * Get all dentists on break
     * 
     * @return Collection|Dentist[]
     */
    public function getDentistsOnBreak(): Collection
    {
        return Dentist::where('status', self::STATE_BREAK)
            ->where('is_active', true)
            ->get();
    }

    /**
     * Get current state of dentist
     * 
     * @param Dentist $dentist
     * @return string
     */
    public function getState(Dentist $dentist): string
    {
        return $dentist->status;
    }

    /**
     * Get human-readable state label
     * 
     * @param string $state
     * @return string
     */
    public function getStateLabel(string $state): string
    {
        return match($state) {
            self::STATE_AVAILABLE => 'Available',
            self::STATE_BUSY => 'Busy',
            self::STATE_BREAK => 'On Break',
            default => 'Unknown',
        };
    }

    /**
     * Get state color for UI display
     * 
     * @param string $state
     * @return string
     */
    public function getStateColor(string $state): string
    {
        return match($state) {
            self::STATE_AVAILABLE => 'success',      // Green
            self::STATE_BUSY => 'warning',           // Yellow/Orange
            self::STATE_BREAK => 'secondary',        // Gray
            default => 'secondary',
        };
    }

    /**
     * Get state icon for UI display
     * 
     * @param string $state
     * @return string Bootstrap Icon name
     */
    public function getStateIcon(string $state): string
    {
        return match($state) {
            self::STATE_AVAILABLE => 'check-circle',     // ✓
            self::STATE_BUSY => 'hourglass',             // ⌛
            self::STATE_BREAK => 'pause-circle',         // ⏸
            default => 'question-circle',
        };
    }

    /**
     * Check if dentist can be assigned a patient
     * 
     * Returns true if dentist is AVAILABLE
     * Returns false if BUSY or on BREAK
     * 
     * @param Dentist $dentist
     * @return bool
     */
    public function canBeAssigned(Dentist $dentist): bool
    {
        return $this->isAvailable($dentist);
    }

    /**
     * Get all dentists grouped by state
     * 
     * Useful for dashboard and reports
     * 
     * @return array
     */
    public function getDentistsByState(): array
    {
        $dentists = Dentist::where('is_active', true)->get();

        return [
            'available' => $dentists->filter(fn($d) => $d->status === self::STATE_AVAILABLE),
            'busy' => $dentists->filter(fn($d) => $d->status === self::STATE_BUSY),
            'break' => $dentists->filter(fn($d) => $d->status === self::STATE_BREAK),
        ];
    }

    /**
     * Get dentist availability statistics
     * 
     * @return array
     */
    public function getAvailabilityStats(): array
    {
        $total = Dentist::where('is_active', true)->count();
        $available = $this->getAvailableDentists()->count();
        $busy = $this->getBusyDentists()->count();
        $onBreak = $this->getDentistsOnBreak()->count();

        return [
            'total' => $total,
            'available' => $available,
            'busy' => $busy,
            'on_break' => $onBreak,
            'working' => $available + $busy,  // Available + Busy
            'unavailable' => $onBreak,         // On Break
            'percentages' => [
                'available' => $total > 0 ? round(($available / $total) * 100, 2) : 0,
                'busy' => $total > 0 ? round(($busy / $total) * 100, 2) : 0,
                'on_break' => $total > 0 ? round(($onBreak / $total) * 100, 2) : 0,
            ]
        ];
    }
}
