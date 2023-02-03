<?php

namespace App\Entity;

use App\Repository\ScheduleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ScheduleRepository::class)]
class Schedule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $classeSchedule = null;

    #[ORM\OneToMany(mappedBy: 'schedule', targetEntity: Subject::class)]
    private Collection $subject_id;

    #[ORM\Column(type: "datetime_immutable", options: ["default" => "CURRENT_TIMESTAMP"])]
    private $created_at;

    #[ORM\OneToMany(mappedBy: 'scheduleId', targetEntity: Student::class)]
    private  Collection $scheduleClasse;

    #[ORM\OneToMany(mappedBy: 'schedule_id', targetEntity: Subject::class)]
    private Collection $subjects;

    public function __construct()
    {
        $this->subject_id = new ArrayCollection();
        $this->scheduleClasse = new ArrayCollection();
        $this->created_at = new \DateTimeImmutable();
        $this->subjects = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClasseSchedule(): ?string
    {
        return $this->classeSchedule;
    }

    public function setClasseSchedule(string $classeSchedule): self
    {
        $this->classeSchedule = $classeSchedule;

        return $this;
    }

    /**
     * @return Collection<int, Subject>
     */
    public function getSubjectId(): Collection
    {
        return $this->subject_id;
    }

    public function addSubjectId(Subject $subjectId): self
    {
        if (!$this->subject_id->contains($subjectId)) {
            $this->subject_id->add($subjectId);
            $subjectId->setSchedule($this);
        }

        return $this;
    }

    public function removeSubjectId(Subject $subjectId): self
    {
        if ($this->subject_id->removeElement($subjectId)) {
            // set the owning side to null (unless already changed)
            if ($subjectId->getSchedule() === $this) {
                $subjectId->setSchedule(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * @return Collection<int, Student>
     */
    public function getScheduleClasse(): Collection
    {
        return $this->scheduleClasse;
    }

    public function addScheduleClasse(Student $scheduleClasse): self
    {
        if (!$this->scheduleClasse->contains($scheduleClasse)) {
            $this->scheduleClasse->add($scheduleClasse);
            $scheduleClasse->setClasseSchId($this);
        }

        return $this;
    }

    public function removeScheduleClasse(Student $scheduleClasse): self
    {
        if ($this->scheduleClasse->removeElement($scheduleClasse)) {
            // set the owning side to null (unless already changed)
            if ($scheduleClasse->getClasseSchId() === $this) {
                $scheduleClasse->setClasseSchId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Subject>
     */
    public function getSubjects(): Collection
    {
        return $this->subjects;
    }

    public function addSubject(Subject $subject): self
    {
        if (!$this->subjects->contains($subject)) {
            $this->subjects->add($subject);
            $subject->setScheduleId($this);
        }

        return $this;
    }

    public function removeSubject(Subject $subject): self
    {
        if ($this->subjects->removeElement($subject)) {
            // set the owning side to null (unless already changed)
            if ($subject->getScheduleId() === $this) {
                $subject->setScheduleId(null);
            }
        }

        return $this;
    }
}
