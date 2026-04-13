import { useState, useEffect, useRef, useMemo, useCallback } from 'react';
import { useForm } from 'react-hook-form';
import { useSearchParams, useNavigate } from 'react-router';
import { toast } from 'sonner';
import { Badge } from '../components/ui/badge';
import { Button } from '../components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '../components/ui/card';
import { Input } from '../components/ui/input';
import { Label } from '../components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../components/ui/select';
import { Textarea } from '../components/ui/textarea';
import {
  Upload,
  Check,
  Loader2,
  ChevronLeft,
  Star,
  Users,
  Video,
  CalendarCheck,
  FileBadge,
} from 'lucide-react';

const MEMBERSHIP_DRAFT_KEY = 'membership_application_draft_v2';

const MEMBERSHIP_TYPES = [
  {
    id: '2',
    label: 'Regular Member',
    description: 'Full voting rights, eligible for all benefits and services.',
    icon: Star,
  },
  {
    id: '1',
    label: 'Associate Member',
    description: 'Limited participation, ideal for those exploring membership.',
    icon: Users,
  },
];

const MEMBERSHIP_TYPE_LABELS: Record<string, string> = {
  '2': 'Regular Member',
  '1': 'Associate Member',
};

const ID_TYPES = [
  'TIN',
  'Philippine National ID (PhilSys ID)',
  'Passport',
  "Driver's License",
  'UMID (SSS/GSIS ID)',
  'PRC ID (for licensed professionals)',
  "Voter's ID (if still available)",
  'Postal ID',
  'Senior Citizen ID',
  'PWD ID',
];

const MONTHLY_INCOME_RANGES = [
  'Below ₱10,000',
  '₱10,000 – ₱20,000',
  '₱20,001 – ₱30,000',
  '₱30,001 – ₱50,000',
  '₱50,001 – ₱100,000',
  'Above ₱100,000',
];

const SOURCE_OF_INCOME_OPTIONS = [
  'Employment',
  'Business',
  'Remittance',
  'Pension/Retirement',
  'Agriculture',
  'Others',
];

type ProfileData = {
  first_name: string;
  middle_name: string;
  last_name: string;
  email: string;
  mobile_number: string;
  birthdate: string;
  sex: string;
  civil_status: string;
  id_type: string;
  id_number: string;
  house_no: string;
  street_barangay: string;
  municipality: string;
  province: string;
  zip_code: string;
  occupation: string;
  employer_name: string;
  monthly_income_range: string;
  source_of_income: string;
  monthly_income: string;
  years_in_business: string;
  emergency_full_name: string;
  emergency_phone: string;
  emergency_relationship: string;
  dependents_count: string;
  children_in_school_count: string;
};

type SpouseData = {
  full_name: string;
  birthdate: string;
  occupation: string;
  employer_name: string;
  source_of_income: string;
  monthly_income: string;
};

type CoMakerData = {
  full_name: string;
  relationship: string;
  contact_number: string;
  address: string;
  occupation: string;
  employer_name: string;
  monthly_income: string;
};

type ApplicationData = {
  application_date: string;
  membership_type_id: string;
  remarks: string;
};

type OrientationProgress = {
  zoom_attended: boolean;
  video_completed: boolean;
  assessment_passed: boolean;
  certificate_generated: boolean;
};

type DraftData = {
  step: number;
  selectedTypeId: string;
  profileData: ProfileData | null;
  applicationData: ApplicationData | null;
  orientationProgress: OrientationProgress;
  spouseData: SpouseData | null;
  coMakersData: CoMakerData[];
  idFileName: string | null;
};

type OrientationQuestion = {
  question: string;
  choices: Array<{ value: string }> | string[];
  correct_answer: string;
};

type OrientationSettings = {
  zoom_link: string;
  video_link: string;
  passing_score: number;
  require_for_loan: boolean;
  questions: OrientationQuestion[];
};

const emptyOrientationProgress: OrientationProgress = {
  zoom_attended: false,
  video_completed: false,
  assessment_passed: false,
  certificate_generated: false,
};

function StepIndicator({ current }: { current: number }) {
  const steps = ['Personal Details', 'Application & Documents', 'Spouse & Co-Makers', 'Orientation'];

  return (
    <div className="flex items-center justify-center mb-10 flex-wrap gap-y-4">
      {steps.map((label, i) => {
        const stepNum = i + 1;
        const done = stepNum < current;
        const active = stepNum === current;

        return (
          <div key={i} className="flex items-center">
            <div className="flex flex-col items-center">
              <div
                className={`
                  w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm border-2 transition-all duration-300
                  ${done ? 'bg-primary border-primary text-white' : ''}
                  ${active ? 'bg-primary border-primary text-white scale-110 shadow-lg shadow-primary/30' : ''}
                  ${!done && !active ? 'bg-background border-border text-muted-foreground' : ''}
                `}
              >
                {done ? <Check className="w-4 h-4" /> : stepNum}
              </div>
              <span className={`mt-2 text-xs font-semibold whitespace-nowrap ${active ? 'text-primary' : 'text-muted-foreground'}`}>
                {label}
              </span>
            </div>
            {i < steps.length - 1 && (
              <div className={`w-14 sm:w-24 h-0.5 mb-5 mx-3 transition-all duration-500 ${done ? 'bg-primary' : 'bg-border'}`} />
            )}
          </div>
        );
      })}
    </div>
  );
}

function FileDropZone({
  label,
  required,
  inputRef,
  onChange,
  fileName,
  helperText,
}: {
  label: string;
  required?: boolean;
  inputRef: React.RefObject<HTMLInputElement>;
  onChange: (file: File | null) => void;
  fileName?: string;
  helperText?: string;
}) {
  const [dragging, setDragging] = useState(false);

  return (
    <div className="space-y-1.5">
      <Label>
        {label}
        {required && <span className="text-destructive ml-0.5">*</span>}
      </Label>
      <div
        onDragOver={(e) => {
          e.preventDefault();
          setDragging(true);
        }}
        onDragLeave={() => setDragging(false)}
        onDrop={(e) => {
          e.preventDefault();
          setDragging(false);
          onChange(e.dataTransfer.files?.[0] ?? null);
        }}
        onClick={() => inputRef.current?.click()}
        className={`
          flex flex-col items-center justify-center gap-1 rounded-lg border-2 border-dashed
          cursor-pointer min-h-[100px] px-4 py-6 text-center transition-colors
          ${dragging ? 'border-primary bg-primary/5' : 'border-border bg-muted/30 hover:bg-muted/50'}
        `}
      >
        <Upload className="w-5 h-5 text-muted-foreground" />
        {fileName ? (
          <span className="text-sm text-primary font-medium">{fileName}</span>
        ) : (
          <span className="text-sm text-muted-foreground">
            Drag & drop or <span className="text-primary font-medium underline-offset-2 hover:underline">Browse</span>
          </span>
        )}
        {helperText && <span className="text-xs text-muted-foreground mt-1">{helperText}</span>}
        <input
          ref={inputRef}
          type="file"
          accept=".pdf,.jpg,.jpeg,.png"
          className="hidden"
          onChange={(e) => onChange(e.target.files?.[0] ?? null)}
        />
      </div>
    </div>
  );
}

function SectionDivider({ label }: { label: string }) {
  return (
    <div className="flex items-center gap-3 pt-2">
      <div className="h-px flex-1 bg-border/60" />
      <span className="text-xs font-semibold uppercase tracking-widest text-muted-foreground">{label}</span>
      <div className="h-px flex-1 bg-border/60" />
    </div>
  );
}

function MembershipTypeCard({
  type,
  selected,
  onSelect,
}: {
  type: typeof MEMBERSHIP_TYPES[0];
  selected: boolean;
  onSelect: () => void;
}) {
  const Icon = type.icon;

  return (
    <button
      type="button"
      onClick={onSelect}
      className={`
        relative w-full text-left rounded-xl border-2 p-4 transition-all duration-200 cursor-pointer
        ${selected ? 'border-primary bg-primary/5 shadow-sm shadow-primary/10' : 'border-border bg-card hover:border-primary/40 hover:bg-muted/30'}
      `}
    >
      <div
        className={`
          absolute top-4 right-4 w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all
          ${selected ? 'border-primary bg-primary' : 'border-muted-foreground/40 bg-background'}
        `}
      >
        {selected && <div className="w-2 h-2 rounded-full bg-white" />}
      </div>

      <div className="flex items-start gap-3 pr-8">
        <div
          className={`
            w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0 transition-colors
            ${selected ? 'bg-primary text-white' : 'bg-muted text-muted-foreground'}
          `}
        >
          <Icon className="w-4 h-4" />
        </div>
        <div>
          <p className={`font-semibold text-sm ${selected ? 'text-primary' : 'text-foreground'}`}>{type.label}</p>
          <p className="text-xs text-muted-foreground mt-0.5 leading-relaxed">{type.description}</p>
        </div>
      </div>
    </button>
  );
}

export function MembershipApply() {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const initialTypeId = searchParams.get('type') ?? '';

  const [step, setStep] = useState(1);
  const [saving, setSaving] = useState(false);
  const [submitted, setSubmitted] = useState(false);
  const [heroVisible, setHeroVisible] = useState(false);
  const [profileData, setProfileData] = useState<ProfileData | null>(null);
  const [applicationData, setApplicationData] = useState<ApplicationData | null>(null);
  const [spouseData, setSpouseData] = useState<SpouseData | null>(null);
  const [coMakersData, setCoMakersData] = useState<CoMakerData[]>([]);

  const [selectedTypeId, setSelectedTypeId] = useState<string>(
    MEMBERSHIP_TYPE_LABELS[initialTypeId] ? initialTypeId : '2'
  );

  const [orientationProgress, setOrientationProgress] = useState<OrientationProgress>(emptyOrientationProgress);
  const [orientationSettings, setOrientationSettings] = useState<OrientationSettings>({
    zoom_link: '',
    video_link: '',
    passing_score: 75,
    require_for_loan: true,
    questions: [],
  });
  const [assessmentAnswers, setAssessmentAnswers] = useState<Record<number, string>>({});
  const [zoomClicked, setZoomClicked] = useState(false);
  const [videoInteracted, setVideoInteracted] = useState(false);
  const [assessmentSubmitted, setAssessmentSubmitted] = useState(false);

  const membershipTypeLabel = MEMBERSHIP_TYPE_LABELS[selectedTypeId] ?? '';

  const [idFileFront, setIdFileFront] = useState<File | null>(null);
  const [idFileBack, setIdFileBack] = useState<File | null>(null);

  const idFrontRef = useRef<HTMLInputElement>(null!);
  const idBackRef = useRef<HTMLInputElement>(null!);

  // IndexedDB helpers for persisting files across sessions
  const dbPromise = useMemo(() => {
    return new Promise<IDBDatabase>((resolve, reject) => {
      const request = indexedDB.open('membership_app', 1);
      request.onupgradeneeded = (e) => {
        const db = (e.target as IDBOpenDBRequest).result;
        if (!db.objectStoreNames.contains('files')) {
          db.createObjectStore('files');
        }
      };
      request.onsuccess = () => resolve(request.result);
      request.onerror = () => reject(request.error);
    });
  }, []);

  const saveFileToDb = useCallback(async (key: string, file: File) => {
    try {
      const db = await dbPromise;
      const tx = db.transaction('files', 'readwrite');
      tx.objectStore('files').put(file, key);
      return new Promise<void>((resolve, reject) => {
        tx.oncomplete = () => resolve();
        tx.onerror = () => reject(tx.error);
      });
    } catch (error) {
      console.error('Failed to save file to IndexedDB:', error);
    }
  }, [dbPromise]);

  const getFileFromDb = useCallback(async (key: string): Promise<File | null> => {
    try {
      const db = await dbPromise;
      const tx = db.transaction('files', 'readonly');
      const request = tx.objectStore('files').get(key);
      return new Promise((resolve) => {
        request.onsuccess = () => resolve(request.result || null);
        request.onerror = () => {
          console.error('Failed to get file from IndexedDB:', request.error);
          resolve(null);
        };
      });
    } catch (error) {
      console.error('Failed to get file from IndexedDB:', error);
      return null;
    }
  }, [dbPromise]);

  const clearFileFromDb = useCallback(async (key: string) => {
    try {
      const db = await dbPromise;
      const tx = db.transaction('files', 'readwrite');
      tx.objectStore('files').delete(key);
    } catch (error) {
      console.error('Failed to clear file from IndexedDB:', error);
    }
  }, [dbPromise]);

  const {
    register: reg1,
    handleSubmit: handle1,
    formState: { errors: err1 },
    setValue: set1,
    watch: watch1,
  } = useForm<ProfileData>();

  const {
    register: reg2,
    handleSubmit: handle2,
    setValue: set2,
    watch: watch2,
  } = useForm<ApplicationData>({
    defaultValues: { application_date: new Date().toISOString().split('T')[0], remarks: '', membership_type_id: selectedTypeId },
  });

  const {
    register: reg3,
    handleSubmit: handle3,
    setValue: set3,
    watch: watch3,
  } = useForm<SpouseData>();

  const {
    register: reg4,
    handleSubmit: handle4,
    setValue: set4,
    watch: watch4,
  } = useForm<CoMakerData>();

  const sourceOfIncome = watch1('source_of_income');
  const sex = watch1('sex');
  const civilStatus = watch1('civil_status');
  const idType = watch1('id_type');
  const monthlyIncomeRange = watch1('monthly_income_range');
  const watchedProfile = watch1();
  const watchedApplication = watch2();

  const normalizedQuestions = useMemo(() => {
    let rawQuestions: any = orientationSettings.questions;

    if (typeof rawQuestions === 'string') {
      try {
        rawQuestions = JSON.parse(rawQuestions);
      } catch {
        rawQuestions = [];
      }
    }

    if (!Array.isArray(rawQuestions)) {
      rawQuestions = [];
    }

    return rawQuestions.map((q: any) => {
      let rawChoices = q?.choices ?? [];

      if (typeof rawChoices === 'string') {
        try {
          rawChoices = JSON.parse(rawChoices);
        } catch {
          rawChoices = [];
        }
      }

      if (!Array.isArray(rawChoices)) {
        rawChoices = [];
      }

      return {
        ...q,
        choices: rawChoices.map((choice: any) =>
          typeof choice === 'string' ? choice : choice?.value ?? ''
        ),
      };
    });
  }, [orientationSettings.questions]);

  const correctCount = useMemo(() => {
    return normalizedQuestions.filter((q: any, index: number) => {
      const selected = (assessmentAnswers[index] ?? '').trim().toLowerCase();
      const correct = (q.correct_answer ?? '').trim().toLowerCase();

      return selected === correct;
    }).length;
  }, [normalizedQuestions, assessmentAnswers]);

  const totalQuestions = normalizedQuestions.length;
  const assessmentScore = totalQuestions > 0 ? Math.round((correctCount / totalQuestions) * 100) : 0;
  const assessmentPassed = totalQuestions > 0 && assessmentScore >= orientationSettings.passing_score;
  const allQuestionsAnswered = totalQuestions > 0 && normalizedQuestions.every((_, idx) => assessmentAnswers[idx] !== undefined && assessmentAnswers[idx] !== '');

  const orientationComplete = useMemo(() => {
    if (!orientationSettings.require_for_loan) return true;

    return (
      zoomClicked &&
      videoInteracted &&
      assessmentSubmitted &&
      assessmentPassed
    );
  }, [zoomClicked, videoInteracted, assessmentSubmitted, assessmentPassed, orientationSettings.require_for_loan]);

  useEffect(() => {
    setOrientationProgress((prev) => ({
      ...prev,
      zoom_attended: zoomClicked,
      video_completed: videoInteracted,
      assessment_passed: assessmentSubmitted && assessmentPassed,
      certificate_generated:
        zoomClicked &&
        videoInteracted &&
        assessmentSubmitted &&
        assessmentPassed,
    }));
  }, [zoomClicked, videoInteracted, assessmentSubmitted, assessmentPassed]);

  useEffect(() => {
    const t = setTimeout(() => setHeroVisible(true), 100);
    return () => clearTimeout(t);
  }, []);

  useEffect(() => {
    fetch('/api/orientation-settings')
      .then((res) => res.json())
      .then((data) => {
        setOrientationSettings({
          zoom_link: data?.zoom_link ?? '',
          video_link: data?.video_link ?? '',
          passing_score: Number(data?.passing_score ?? 75),
          require_for_loan: Boolean(data?.require_for_loan ?? true),
          questions: Array.isArray(data?.questions) || typeof data?.questions === 'string'
            ? data.questions
            : [],
        });
      })
      .catch(() => {
        toast.error('Failed to load orientation settings.');
      });

    // Load YouTube IFrame API
    const tag = document.createElement('script');
    tag.src = 'https://www.youtube.com/iframe_api';
    const firstScriptTag = document.getElementsByTagName('script')[0];
    firstScriptTag?.parentNode?.insertBefore(tag, firstScriptTag);
  }, []);

  useEffect(() => {
    if (!initialTypeId || !MEMBERSHIP_TYPE_LABELS[initialTypeId]) {
      toast.error('Please select a membership type first.');
      navigate('/membership/info');
    }
  }, [initialTypeId, navigate]);

  useEffect(() => {
    if (step !== 4 || !orientationSettings.video_link || videoInteracted) return;

    // Extract video ID from embed URL
    const match = orientationSettings.video_link.match(/embed\/([a-zA-Z0-9_-]+)/);
    if (!match?.[1]) return;

    const videoId = match[1];

    const onPlayerStateChange = (event: any) => {
      if (event.data === 0) {
        setVideoInteracted(true);
        toast.success('Video watched! You can now proceed with the assessment.');
      }
    };

    const initPlayer = () => {
      const container = document.getElementById('orientation-video-player');
      if (!container) return;
      new (window as any).YT.Player('orientation-video-player', {
        videoId: videoId,
        width: container.offsetWidth || '100%',
        height: Math.round((container.offsetWidth || 640) * 9 / 16),
        playerVars: { rel: 0, modestbranding: 1 },
        events: { onStateChange: onPlayerStateChange },
      });
    };

    if (typeof (window as any).YT !== 'undefined' && (window as any).YT.Player) {
      initPlayer();
      return;
    }

    const checkYT = setInterval(() => {
      if (typeof (window as any).YT !== 'undefined' && (window as any).YT.Player) {
        clearInterval(checkYT);
        initPlayer();
      }
    }, 100);

    return () => clearInterval(checkYT);
  }, [step, orientationSettings.video_link, videoInteracted]);

  useEffect(() => {
    const raw = localStorage.getItem(MEMBERSHIP_DRAFT_KEY);
    if (!raw) return;

    try {
      const draft: DraftData = JSON.parse(raw);

      if (draft.selectedTypeId && MEMBERSHIP_TYPE_LABELS[draft.selectedTypeId]) {
        setSelectedTypeId(draft.selectedTypeId);
      }

      if (draft.profileData) {
        setProfileData(draft.profileData);
        Object.entries(draft.profileData).forEach(([key, value]) => {
          set1(key as keyof ProfileData, value ?? '');
        });
      }

      if (draft.applicationData) {
        setApplicationData(draft.applicationData);
        set2('application_date', draft.applicationData.application_date ?? new Date().toISOString().split('T')[0]);
        set2('remarks', draft.applicationData.remarks ?? '');
        set2('membership_type_id', draft.applicationData.membership_type_id ?? draft.selectedTypeId ?? selectedTypeId);
      }

      if (draft.spouseData) {
        setSpouseData(draft.spouseData);
        Object.entries(draft.spouseData).forEach(([key, value]) => {
          set3(key as keyof SpouseData, value ?? '');
        });
      }

      if (draft.coMakersData && draft.coMakersData.length > 0) {
        setCoMakersData(draft.coMakersData);
      }

      if (draft.orientationProgress) {
        setOrientationProgress(draft.orientationProgress);
      }

      setStep(draft.step ?? 1);

      // Restore ID files from IndexedDB
      if (draft.idFileName) {
        getFileFromDb('id_file_front').then((file) => {
          if (file) {
            setIdFileFront(file);
          }
        });
        getFileFromDb('id_file_back').then((file) => {
          if (file) {
            setIdFileBack(file);
          }
        });
      }
    } catch {
      console.error('Failed to restore draft');
    }
  }, [set1, set2, set3, selectedTypeId, getFileFromDb]);

  useEffect(() => {
    const timeout = setTimeout(async () => {
      // Get latest form values from all forms
      const currentProfile = { ...watchedProfile, ...profileData };
      const currentApplication = { ...watchedApplication, ...applicationData };
      const currentSpouse = watch3();
      
      const draft: DraftData = {
        step,
        selectedTypeId,
        profileData: currentProfile as ProfileData,
        applicationData: currentApplication as ApplicationData,
        spouseData: (Object.values(currentSpouse).some(v => v) ? currentSpouse : null) as SpouseData | null,
        coMakersData,
        orientationProgress,
        idFileName: (idFileFront?.name || idFileBack?.name) ?? null,
      };

      localStorage.setItem(MEMBERSHIP_DRAFT_KEY, JSON.stringify(draft));

      // Save ID files to IndexedDB
      if (idFileFront) {
        await saveFileToDb('id_file_front', idFileFront);
      }
      if (idFileBack) {
        await saveFileToDb('id_file_back', idFileBack);
      }
    }, 500);

    return () => clearTimeout(timeout);
  }, [
    step,
    selectedTypeId,
    profileData,
    applicationData,
    spouseData,
    coMakersData,
    orientationProgress,
    watchedProfile,
    watchedApplication,
    watch3,
    idFileFront,
    idFileBack,
    saveFileToDb,
  ]);

  const submitProfile = (data: ProfileData) => {
    setProfileData(data);
    toast.success('Personal details saved.');
    setStep(2);
  };

  const saveApplicationStep = (data: ApplicationData) => {
    const finalData = {
      ...data,
      membership_type_id: selectedTypeId,
    };

    setApplicationData(finalData);
    toast.success('Application details saved.');
    setStep(3);
  };

  const handleZoomClick = () => {
    setZoomClicked(true);
    window.open(orientationSettings.zoom_link, '_blank');
  };

  const submitAssessment = () => {
    if (allQuestionsAnswered) {
      setAssessmentSubmitted(true);
      toast.success('Assessment submitted! Your score is shown below.');
    } else {
      toast.error('Please answer all questions before submitting.');
    }
  };

  const submitFinalApplication = async () => {
    if (!profileData) {
      toast.error('Personal data missing. Please go back to step 1.');
      return;
    }

    if (!applicationData) {
      toast.error('Application details missing. Please go back to step 2.');
      return;
    }

    if (!orientationComplete) {
      toast.error('Please complete the orientation first.');
      return;
    }

    if (!idFileFront || !idFileBack) {
      toast.error('Please upload both front and back of your ID.');
      return;
    }

    setSaving(true);

    try {
      const formData = new FormData();

      // Profile data - skip empty values
      Object.entries(profileData).forEach(([k, v]) => {
        if (v && String(v).trim()) {
          formData.append(k, String(v));
        }
      });

      // Application data
      formData.append('membership_type_id', selectedTypeId);
      formData.append('application_date', applicationData.application_date);
      if (applicationData.remarks && applicationData.remarks.trim()) {
        formData.append('remarks', applicationData.remarks);
      }

      // Spouse data - only send if has content
      if (spouseData && spouseData.full_name && spouseData.full_name.trim()) {
        Object.entries(spouseData).forEach(([k, v]) => {
          if (v && String(v).trim()) {
            formData.append(`spouse_${k}`, String(v));
          }
        });
      }

      // Co-makers data - filter out empty entries
      const validCoMakers = coMakersData.filter(cm => cm.full_name && cm.full_name.trim());
      if (validCoMakers.length > 0) {
        formData.append('co_makers', JSON.stringify(validCoMakers));
      }

      // Orientation data
      formData.append('orientation_zoom_attended', String(orientationProgress.zoom_attended));
      formData.append('orientation_video_completed', String(orientationProgress.video_completed));
      formData.append('orientation_assessment_passed', String(orientationProgress.assessment_passed));
      formData.append('orientation_certificate_generated', String(orientationProgress.certificate_generated));
      if (assessmentScore) {
        formData.append('orientation_score', String(assessmentScore));
      }

      if (idFileFront) formData.append('id_document_front', idFileFront);
      if (idFileBack) formData.append('id_document_back', idFileBack);

      // Debug log
      console.log('Submitting form with data:');
      for (let [key, value] of formData.entries()) {
        if (!(value instanceof File)) {
          console.log(`  ${key}: ${value}`);
        }
      }

      const res = await fetch('/api/membership-application', {
        method: 'POST',
        headers: { Accept: 'application/json' },
        body: formData,
      });

      const json = await res.json();

      if (!res.ok) {
        const errors = json.errors ?? {};
        const firstKey = Object.keys(errors)[0];
        const firstErr = firstKey ? (errors[firstKey] as string[])[0] : null;
        const errorMsg = firstErr ?? json.error ?? json.message ?? 'Submission failed. Please try again.';
        console.error('Submission error:', { status: res.status, errors, message: json.message, error: json.error, fullResponse: json });
        toast.error(`${json.message}${json.error ? '\n' + json.error : ''}`);
        return;
      }

      setSubmitted(true);
      localStorage.removeItem(MEMBERSHIP_DRAFT_KEY);
      toast.success('Application submitted successfully!');
    } catch (err) {
      console.error('Network error:', err);
      toast.error('Network error. Your draft is still saved in this browser.');
    } finally {
      setSaving(false);
    }
  };

  const resetForm = () => {
    setSubmitted(false);
    setStep(1);
    setProfileData(null);
    setApplicationData(null);
    setSpouseData(null);
    setCoMakersData([]);
    setOrientationProgress(emptyOrientationProgress);
    setAssessmentAnswers({});
    setZoomClicked(false);
    setVideoInteracted(false);
    setAssessmentSubmitted(false);
    setIdFileFront(null);
    setIdFileBack(null);
    clearFileFromDb('id_file_front');
    clearFileFromDb('id_file_back');
    localStorage.removeItem(MEMBERSHIP_DRAFT_KEY);
    window.location.reload();
  };

  const clearDraft = () => {
    localStorage.removeItem(MEMBERSHIP_DRAFT_KEY);
    clearFileFromDb('id_file_front');
    clearFileFromDb('id_file_back');
    toast.success('Saved draft cleared.');
    window.location.reload();
  };

  if (submitted) {
    return (
      <div className="flex flex-col">
        <HeroSection heroVisible={heroVisible} membershipTypeLabel={membershipTypeLabel} />
        <section className="py-28 flex items-center justify-center">
          <Card className="max-w-md w-full mx-4 rounded-2xl shadow-lg text-center">
            <CardContent className="pt-10 pb-10 space-y-4">
              <div className="flex items-center justify-center w-16 h-16 rounded-full bg-green-100 mx-auto">
                <Check className="w-8 h-8 text-green-600" />
              </div>
              <h2 className="text-2xl font-bold">Application Submitted!</h2>
              <p className="text-muted-foreground">
                Thank you for applying as a <strong>{membershipTypeLabel}</strong>.
                We will review your application and contact you within 5–7 business days.
              </p>
              <Button onClick={resetForm} variant="outline" className="mt-4">
                Submit Another Application
              </Button>
            </CardContent>
          </Card>
        </section>
      </div>
    );
  }

  if (!initialTypeId || !MEMBERSHIP_TYPE_LABELS[initialTypeId]) return null;

  return (
    <div className="flex flex-col">
      <HeroSection heroVisible={heroVisible} membershipTypeLabel={membershipTypeLabel} />

      <section className="relative py-16 sm:py-24 overflow-hidden">
        <div className="absolute inset-0 bg-[url('/src/images/bghd.jpg')] bg-cover bg-center opacity-5" />
        <div className="absolute inset-0 bg-background/95" />
        <div className="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex items-center justify-center mb-4">
            <span className="inline-flex items-center gap-2 bg-primary/10 border border-primary/20 text-primary text-sm font-semibold px-5 py-2 rounded-full">
              <Check className="w-4 h-4" />
              Applying as: {membershipTypeLabel}
            </span>
          </div>

          <div className="flex justify-center mb-8">
            <Button type="button" variant="outline" onClick={clearDraft}>
              Clear Saved Draft
            </Button>
          </div>

          <StepIndicator current={step} />

          {step === 1 && (
            <form onSubmit={handle1(submitProfile)}>
              <Card className="rounded-2xl bg-card/90 backdrop-blur-sm border-border/50 shadow-sm">
                <CardHeader className="pb-4 border-b border-border/40">
                  <div className="flex items-center gap-3">
                    <div className="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-white text-sm font-bold">1</div>
                    <CardTitle className="text-base font-semibold">Personal Details</CardTitle>
                  </div>
                  <p className="text-sm text-muted-foreground mt-1">Fill in your personal information accurately.</p>
                </CardHeader>

                <CardContent className="space-y-5 pt-6">
                  <SectionDivider label="Basic Information" />

                  <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div className="space-y-1.5">
                      <Label>First name <span className="text-destructive">*</span></Label>
                      <Input {...reg1('first_name', { required: 'Required' })} className="rounded-lg" />
                      {err1.first_name && <p className="text-xs text-destructive">{err1.first_name.message}</p>}
                    </div>
                    <div className="space-y-1.5">
                      <Label>Middle name</Label>
                      <Input {...reg1('middle_name')} className="rounded-lg" />
                    </div>
                    <div className="space-y-1.5">
                      <Label>Last name <span className="text-destructive">*</span></Label>
                      <Input {...reg1('last_name', { required: 'Required' })} className="rounded-lg" />
                      {err1.last_name && <p className="text-xs text-destructive">{err1.last_name.message}</p>}
                    </div>
                  </div>

                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div className="space-y-1.5">
                      <Label>Email <span className="text-destructive">*</span></Label>
                      <Input type="email" {...reg1('email', { required: 'Required' })} className="rounded-lg" />
                      {err1.email && <p className="text-xs text-destructive">{err1.email.message}</p>}
                    </div>
                    <div className="space-y-1.5">
                      <Label>Mobile number <span className="text-destructive">*</span></Label>
                      <Input
                        type="tel"
                        maxLength={11}
                        {...reg1('mobile_number', {
                          required: 'Required',
                          pattern: { value: /^09\d{9}$/, message: 'Must be a valid PH number (09XXXXXXXXX)' },
                        })}
                        className="rounded-lg"
                      />
                      {err1.mobile_number && <p className="text-xs text-destructive">{err1.mobile_number.message}</p>}
                    </div>
                  </div>

                  <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div className="space-y-1.5">
                      <Label>Birthdate <span className="text-destructive">*</span></Label>
                      <Input type="date" {...reg1('birthdate', { required: 'Required' })} className="rounded-lg" />
                      {err1.birthdate && <p className="text-xs text-destructive">{err1.birthdate.message}</p>}
                    </div>

                    <div className="space-y-1.5">
                      <Label>Sex <span className="text-destructive">*</span></Label>
                      <input type="hidden" {...reg1('sex', { required: 'Required' })} />
                      <Select value={sex || ''} onValueChange={(v) => set1('sex', v, { shouldValidate: true, shouldDirty: true })}>
                        <SelectTrigger className="rounded-lg"><SelectValue placeholder="Select" /></SelectTrigger>
                        <SelectContent>
                          <SelectItem value="Male">Male</SelectItem>
                          <SelectItem value="Female">Female</SelectItem>
                        </SelectContent>
                      </Select>
                      {err1.sex && <p className="text-xs text-destructive">{err1.sex.message}</p>}
                    </div>

                    <div className="space-y-1.5">
                      <Label>Civil status <span className="text-destructive">*</span></Label>
                      <input type="hidden" {...reg1('civil_status', { required: 'Required' })} />
                      <Select value={civilStatus || ''} onValueChange={(v) => set1('civil_status', v, { shouldValidate: true, shouldDirty: true })}>
                        <SelectTrigger className="rounded-lg"><SelectValue placeholder="Select" /></SelectTrigger>
                        <SelectContent>
                          <SelectItem value="Single">Single</SelectItem>
                          <SelectItem value="Married">Married</SelectItem>
                          <SelectItem value="Widowed">Widowed</SelectItem>
                          <SelectItem value="Separated">Separated</SelectItem>
                          <SelectItem value="Annulled">Annulled</SelectItem>
                        </SelectContent>
                      </Select>
                      {err1.civil_status && <p className="text-xs text-destructive">{err1.civil_status.message}</p>}
                    </div>
                  </div>

                  <SectionDivider label="Identification" />

                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div className="space-y-1.5">
                      <Label>ID Type <span className="text-destructive">*</span></Label>
                      <input type="hidden" {...reg1('id_type', { required: 'Required' })} />
                      <Select value={idType || ''} onValueChange={(v) => set1('id_type', v, { shouldValidate: true, shouldDirty: true })}>
                        <SelectTrigger className="rounded-lg"><SelectValue placeholder="Select ID type" /></SelectTrigger>
                        <SelectContent>
                          {ID_TYPES.map((t) => (
                            <SelectItem key={t} value={t}>{t}</SelectItem>
                          ))}
                        </SelectContent>
                      </Select>
                      {err1.id_type && <p className="text-xs text-destructive">{err1.id_type.message}</p>}
                    </div>

                    <div className="space-y-1.5">
                      <Label>ID Number <span className="text-destructive">*</span></Label>
                      <Input {...reg1('id_number', { required: 'Required' })} className="rounded-lg" />
                      {err1.id_number && <p className="text-xs text-destructive">{err1.id_number.message}</p>}
                    </div>
                  </div>

                  <SectionDivider label="Address" />

                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div className="space-y-1.5">
                      <Label>House No. <span className="text-destructive">*</span></Label>
                      <Input {...reg1('house_no', { required: 'Required' })} className="rounded-lg" />
                      {err1.house_no && <p className="text-xs text-destructive">{err1.house_no.message}</p>}
                    </div>
                    <div className="space-y-1.5">
                      <Label>Street / Barangay <span className="text-destructive">*</span></Label>
                      <Input {...reg1('street_barangay', { required: 'Required' })} className="rounded-lg" />
                      {err1.street_barangay && <p className="text-xs text-destructive">{err1.street_barangay.message}</p>}
                    </div>
                  </div>

                  <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div className="space-y-1.5">
                      <Label>Municipality / City <span className="text-destructive">*</span></Label>
                      <Input {...reg1('municipality', { required: 'Required' })} className="rounded-lg" />
                      {err1.municipality && <p className="text-xs text-destructive">{err1.municipality.message}</p>}
                    </div>
                    <div className="space-y-1.5">
                      <Label>Province <span className="text-destructive">*</span></Label>
                      <Input {...reg1('province', { required: 'Required' })} className="rounded-lg" />
                      {err1.province && <p className="text-xs text-destructive">{err1.province.message}</p>}
                    </div>
                    <div className="space-y-1.5">
                      <Label>Zip code <span className="text-destructive">*</span></Label>
                      <Input
                        {...reg1('zip_code', {
                          required: 'Required',
                          pattern: { value: /^\d{4}$/, message: 'Must be a 4-digit zip code' },
                        })}
                        className="rounded-lg"
                      />
                      {err1.zip_code && <p className="text-xs text-destructive">{err1.zip_code.message}</p>}
                    </div>
                  </div>

                  <SectionDivider label="Employment & Income" />

                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div className="space-y-1.5">
                      <Label>Occupation <span className="text-destructive">*</span></Label>
                      <Input {...reg1('occupation', { required: 'Required' })} className="rounded-lg" />
                      {err1.occupation && <p className="text-xs text-destructive">{err1.occupation.message}</p>}
                    </div>
                    <div className="space-y-1.5">
                      <Label>Employer / Business Name</Label>
                      <Input {...reg1('employer_name')} className="rounded-lg" />
                    </div>
                  </div>

                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div className="space-y-1.5">
                      <Label>Source of Income <span className="text-destructive">*</span></Label>
                      <input type="hidden" {...reg1('source_of_income', { required: 'Required' })} />
                      <Select value={sourceOfIncome || ''} onValueChange={(v) => set1('source_of_income', v, { shouldValidate: true, shouldDirty: true })}>
                        <SelectTrigger className="rounded-lg"><SelectValue placeholder="Select source" /></SelectTrigger>
                        <SelectContent>
                          {SOURCE_OF_INCOME_OPTIONS.map((s) => (
                            <SelectItem key={s} value={s}>{s}</SelectItem>
                          ))}
                        </SelectContent>
                      </Select>
                      {err1.source_of_income && <p className="text-xs text-destructive">{err1.source_of_income.message}</p>}
                    </div>

                    <div className="space-y-1.5">
                      <Label>Monthly Income Range <span className="text-destructive">*</span></Label>
                      <input type="hidden" {...reg1('monthly_income_range', { required: 'Required' })} />
                      <Select value={monthlyIncomeRange || ''} onValueChange={(v) => set1('monthly_income_range', v, { shouldValidate: true, shouldDirty: true })}>
                        <SelectTrigger className="rounded-lg"><SelectValue placeholder="Select range" /></SelectTrigger>
                        <SelectContent>
                          {MONTHLY_INCOME_RANGES.map((r) => (
                            <SelectItem key={r} value={r}>{r}</SelectItem>
                          ))}
                        </SelectContent>
                      </Select>
                      {err1.monthly_income_range && <p className="text-xs text-destructive">{err1.monthly_income_range.message}</p>}
                    </div>
                  </div>

                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div className="space-y-1.5">
                      <Label>Monthly Income (₱) <span className="text-destructive">*</span></Label>
                      <Input type="number" min={0} {...reg1('monthly_income', { required: 'Required' })} className="rounded-lg" />
                      {err1.monthly_income && <p className="text-xs text-destructive">{err1.monthly_income.message}</p>}
                    </div>
                    {sourceOfIncome === 'Business' && (
                      <div className="space-y-1.5">
                        <Label>Years in Business</Label>
                        <Input type="number" min={0} {...reg1('years_in_business')} className="rounded-lg" />
                      </div>
                    )}
                  </div>

                  <SectionDivider label="Family & Dependents" />

                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div className="space-y-1.5">
                      <Label>Number of Dependents</Label>
                      <Input type="number" min={0} {...reg1('dependents_count')} className="rounded-lg" />
                    </div>
                    <div className="space-y-1.5">
                      <Label>Children Currently in School</Label>
                      <Input type="number" min={0} {...reg1('children_in_school_count')} className="rounded-lg" />
                    </div>
                  </div>

                  <SectionDivider label="Emergency Contact" />

                  <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div className="space-y-1.5">
                      <Label>Full Name <span className="text-destructive">*</span></Label>
                      <Input {...reg1('emergency_full_name', { required: 'Required' })} className="rounded-lg" />
                      {err1.emergency_full_name && <p className="text-xs text-destructive">{err1.emergency_full_name.message}</p>}
                    </div>
                    <div className="space-y-1.5">
                      <Label>Phone Number <span className="text-destructive">*</span></Label>
                      <Input
                        type="tel"
                        maxLength={11}
                        {...reg1('emergency_phone', {
                          required: 'Required',
                          pattern: { value: /^09\d{9}$/, message: 'Must be a valid PH number' },
                        })}
                        className="rounded-lg"
                      />
                      {err1.emergency_phone && <p className="text-xs text-destructive">{err1.emergency_phone.message}</p>}
                    </div>
                    <div className="space-y-1.5">
                      <Label>Relationship <span className="text-destructive">*</span></Label>
                      <Input {...reg1('emergency_relationship', { required: 'Required' })} className="rounded-lg" />
                      {err1.emergency_relationship && <p className="text-xs text-destructive">{err1.emergency_relationship.message}</p>}
                    </div>
                  </div>
                </CardContent>
              </Card>

              <div className="flex justify-end mt-6">
                <Button type="submit" className="bg-primary hover:bg-primary/90 text-white rounded-lg px-8 font-medium">
                  Save & Continue
                </Button>
              </div>
            </form>
          )}

          {step === 2 && (
            <form onSubmit={handle2(saveApplicationStep)}>
              <div className="flex flex-col lg:flex-row gap-6">
                <div className="flex-1">
                  <Card className="rounded-2xl bg-card/90 backdrop-blur-sm border-border/50 shadow-sm">
                    <CardHeader className="pb-4 border-b border-border/40">
                      <div className="flex items-center gap-3">
                        <div className="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-white text-sm font-bold">2</div>
                        <CardTitle className="text-base font-semibold">Application Details</CardTitle>
                      </div>
                      <p className="text-sm text-muted-foreground mt-1">Review and save before orientation.</p>
                    </CardHeader>

                    <CardContent className="space-y-5 pt-6">
                      <div className="space-y-2">
                        <Label>Membership Type</Label>
                        {MEMBERSHIP_TYPES.filter(type => type.id === selectedTypeId).map((type) => {
                          const Icon = type.icon;
                          return (
                            <div key={type.id} className="relative w-full text-left rounded-xl border-2 border-primary bg-primary/5 p-4">
                              <div className="flex items-start gap-3">
                                <div className="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0 bg-primary text-white">
                                  <Icon className="w-4 h-4" />
                                </div>
                                <div>
                                  <p className="font-semibold text-sm text-primary">{type.label}</p>
                                  <p className="text-xs text-muted-foreground mt-0.5 leading-relaxed">{type.description}</p>
                                </div>
                              </div>
                            </div>
                          );
                        })}
                      </div>

                      <div className="space-y-1.5">
                        <Label>Application Date</Label>
                        <Input type="date" {...reg2('application_date')} className="rounded-lg bg-muted text-muted-foreground cursor-not-allowed" readOnly />
                      </div>

                      <div className="space-y-1.5">
                        <Label>Remarks <span className="text-muted-foreground text-xs">(optional)</span></Label>
                        <Textarea {...reg2('remarks')} placeholder="Any additional notes..." rows={3} className="rounded-lg" />
                      </div>
                    </CardContent>
                  </Card>
                </div>

                <div className="lg:w-72 xl:w-80">
                  <Card className="rounded-2xl bg-card/90 backdrop-blur-sm border-border/50 shadow-sm h-full">
                    <CardHeader className="pb-4 border-b border-border/40">
                      <CardTitle className="text-base font-semibold">Documents</CardTitle>
                      <p className="text-sm text-muted-foreground mt-1">Upload your ID (front and back).</p>
                    </CardHeader>

                    <CardContent className="space-y-5 pt-6">
                      {profileData?.id_type && (
                        <div className="rounded-lg bg-blue-50 border border-blue-200 p-3 mb-4">
                          <p className="text-xs text-blue-900 font-medium">ID Type Selected</p>
                          <p className="text-sm text-blue-800 font-semibold">{profileData.id_type}</p>
                        </div>
                      )}
                      
                      <FileDropZone 
                        label="ID Front" 
                        required 
                        inputRef={idFrontRef} 
                        fileName={idFileFront?.name} 
                        onChange={setIdFileFront} 
                        helperText="Front side of your ID"
                      />

                      <FileDropZone 
                        label="ID Back" 
                        required 
                        inputRef={idBackRef} 
                        fileName={idFileBack?.name} 
                        onChange={setIdFileBack} 
                        helperText="Back side of your ID"
                      />
                    </CardContent>
                  </Card>
                </div>
              </div>

              <div className="flex items-center justify-between mt-6">
                <Button type="button" variant="outline" onClick={() => setStep(1)} className="rounded-lg px-6 font-medium gap-2">
                  <ChevronLeft className="w-4 h-4" /> Back
                </Button>
                <Button type="submit" className="bg-primary hover:bg-primary/90 text-white rounded-lg px-8 font-medium">
                  Save & Continue to Spouse & Co-Makers
                </Button>
              </div>
            </form>
          )}

          {step === 3 && (
            <form onSubmit={handle3((data) => {
              setSpouseData(data);
              toast.success('Spouse information saved.');
            })}>
              <Card className="rounded-2xl bg-card/90 backdrop-blur-sm border-border/50 shadow-sm">
                <CardHeader className="pb-4 border-b border-border/40">
                  <div className="flex items-center gap-3">
                    <div className="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-white text-sm font-bold">3</div>
                    <CardTitle className="text-base font-semibold">Spouse & Co-Makers</CardTitle>
                  </div>
                  <p className="text-sm text-muted-foreground mt-1">Add spouse and co-maker information (optional).</p>
                </CardHeader>

                <CardContent className="space-y-5 pt-6">
                  {civilStatus === 'Married' && (
                    <>
                      <SectionDivider label="Spouse Information" />
                      <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div className="space-y-1.5">
                          <Label>Full Name</Label>
                          <Input {...reg3('full_name')} className="rounded-lg" />
                        </div>
                        <div className="space-y-1.5">
                          <Label>Birthdate</Label>
                          <Input type="date" {...reg3('birthdate')} className="rounded-lg" />
                        </div>
                        <div className="space-y-1.5">
                          <Label>Occupation</Label>
                          <Input {...reg3('occupation')} className="rounded-lg" />
                        </div>
                      </div>

                      <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div className="space-y-1.5">
                          <Label>Employer</Label>
                          <Input {...reg3('employer_name')} className="rounded-lg" />
                        </div>
                        <div className="space-y-1.5">
                          <Label>Source of Income</Label>
                          <Input {...reg3('source_of_income')} className="rounded-lg" />
                        </div>
                      </div>

                      <div className="space-y-1.5">
                        <Label>Monthly Income (₱)</Label>
                        <Input type="number" min={0} {...reg3('monthly_income')} className="rounded-lg" />
                      </div>
                    </>
                  )}

                  <SectionDivider label="Co-Makers / Guarantors" />

                  <div className="space-y-4">
                    {coMakersData.map((coMaker, idx) => (
                      <div key={idx} className="border border-border rounded-lg p-4 space-y-3">
                        <div className="flex items-center justify-between mb-3">
                          <h4 className="font-semibold text-sm">Co-Maker {idx + 1}</h4>
                          <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            onClick={() => {
                              setCoMakersData(coMakersData.filter((_, i) => i !== idx));
                            }}
                          >
                            Remove
                          </Button>
                        </div>
                        
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                          <div className="space-y-1.5">
                            <Label className="text-sm">Full Name</Label>
                            <Input
                              value={coMaker.full_name}
                              onChange={(e) => {
                                const updated = [...coMakersData];
                                updated[idx].full_name = e.target.value;
                                setCoMakersData(updated);
                              }}
                              className="rounded-lg text-sm"
                            />
                          </div>
                          <div className="space-y-1.5">
                            <Label className="text-sm">Relationship</Label>
                            <Input
                              value={coMaker.relationship}
                              onChange={(e) => {
                                const updated = [...coMakersData];
                                updated[idx].relationship = e.target.value;
                                setCoMakersData(updated);
                              }}
                              className="rounded-lg text-sm"
                            />
                          </div>
                        </div>

                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                          <div className="space-y-1.5">
                            <Label className="text-sm">Contact Number</Label>
                            <Input
                              type="tel"
                              value={coMaker.contact_number}
                              onChange={(e) => {
                                const updated = [...coMakersData];
                                updated[idx].contact_number = e.target.value;
                                setCoMakersData(updated);
                              }}
                              className="rounded-lg text-sm"
                            />
                          </div>
                          <div className="space-y-1.5">
                            <Label className="text-sm">Occupation</Label>
                            <Input
                              value={coMaker.occupation}
                              onChange={(e) => {
                                const updated = [...coMakersData];
                                updated[idx].occupation = e.target.value;
                                setCoMakersData(updated);
                              }}
                              className="rounded-lg text-sm"
                            />
                          </div>
                        </div>

                        <div className="space-y-1.5">
                          <Label className="text-sm">Address</Label>
                          <Input
                            value={coMaker.address}
                            onChange={(e) => {
                              const updated = [...coMakersData];
                              updated[idx].address = e.target.value;
                              setCoMakersData(updated);
                            }}
                            className="rounded-lg text-sm"
                          />
                        </div>

                        <div className="space-y-1.5">
                          <Label className="text-sm">Employer</Label>
                          <Input
                            value={coMaker.employer_name}
                            onChange={(e) => {
                              const updated = [...coMakersData];
                              updated[idx].employer_name = e.target.value;
                              setCoMakersData(updated);
                            }}
                            className="rounded-lg text-sm"
                          />
                        </div>

                        <div className="space-y-1.5">
                          <Label className="text-sm">Monthly Income (₱)</Label>
                          <Input
                            type="number"
                            min={0}
                            value={coMaker.monthly_income}
                            onChange={(e) => {
                              const updated = [...coMakersData];
                              updated[idx].monthly_income = e.target.value;
                              setCoMakersData(updated);
                            }}
                            className="rounded-lg text-sm"
                          />
                        </div>
                      </div>
                    ))}

                    <Button
                      type="button"
                      variant="outline"
                      onClick={() => {
                        setCoMakersData([
                          ...coMakersData,
                          {
                            full_name: '',
                            relationship: '',
                            contact_number: '',
                            address: '',
                            occupation: '',
                            employer_name: '',
                            monthly_income: '',
                          },
                        ]);
                      }}
                      className="w-full"
                    >
                      + Add Co-Maker
                    </Button>
                  </div>
                </CardContent>
              </Card>

              <div className="flex items-center justify-between mt-6">
                <Button type="button" variant="outline" onClick={() => setStep(2)} className="rounded-lg px-6 font-medium gap-2">
                  <ChevronLeft className="w-4 h-4" /> Back
                </Button>
                <Button
                  type="button"
                  onClick={() => {
                    setSpouseData(watch3() as SpouseData);
                    toast.success('Spouse and co-maker information saved.');
                    setStep(4);
                  }}
                  className="bg-primary hover:bg-primary/90 text-white rounded-lg px-8 font-medium"
                >
                  Save & Continue to Orientation
                </Button>
              </div>
            </form>
          )}

          {step === 4 && (
            <div className="space-y-6">
              <Card className="rounded-2xl bg-card/90 backdrop-blur-sm border-border/50 shadow-sm">
                <CardHeader className="pb-4 border-b border-border/40">
                  <div className="flex items-center gap-3">
                    <div className="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-white text-sm font-bold">4</div>
                    <CardTitle className="text-base font-semibold">Orientation</CardTitle>
                  </div>
                  <p className="text-sm text-muted-foreground mt-1">
                    Complete the orientation before submitting your application.
                  </p>
                </CardHeader>

                <CardContent className="space-y-6 pt-6">
                  <div className="grid md:grid-cols-2 gap-4">
                    <div className="rounded-xl border border-border p-4">
                      <div className="flex items-center gap-2 mb-2">
                        <CalendarCheck className="w-5 h-5 text-primary" />
                        <h3 className="font-semibold">Zoom Orientation</h3>
                      </div>
                      <p className="text-sm text-muted-foreground mb-3">
                        Attend the Zoom pre-membership orientation.
                      </p>

                      {orientationSettings.zoom_link ? (
                        <button
                          onClick={handleZoomClick}
                          className="inline-flex items-center justify-center w-full rounded-lg bg-primary text-white py-3 font-medium hover:bg-primary/90 transition-colors"
                        >
                          {zoomClicked ? '✓ Zoom Link Accessed' : 'Join Zoom Orientation'}
                        </button>
                      ) : (
                        <div className="w-full rounded-lg border bg-muted flex items-center justify-center text-sm text-muted-foreground px-4 py-8 text-center">
                          No Zoom link configured yet.
                        </div>
                      )}

                      <p className="text-xs text-muted-foreground mt-3">
                        {zoomClicked ? '✓ Thank you for attending the Zoom orientation.' : 'Click the button above to join the Zoom meeting.'}
                      </p>
                    </div>

                    <div className="rounded-xl border border-border p-4">
                      <div className="flex items-center gap-2 mb-2">
                        <Video className="w-5 h-5 text-primary" />
                        <h3 className="font-semibold">Orientation Video</h3>
                      </div>
                      <p className="text-sm text-muted-foreground mb-3">
                        Watch the orientation video completely. It will automatically mark as watched when finished.
                      </p>

                      {orientationSettings.video_link ? (
                        <div className="space-y-3">
                          <div className="w-full bg-black rounded-lg overflow-hidden" style={{ aspectRatio: '16/9' }}>
                            <div id="orientation-video-player" className="w-full h-full" />
                          </div>
                          {videoInteracted ? (
                            <div className="w-full rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-center text-sm text-green-700 font-medium">
                              ✓ Video watched completely
                            </div>
                          ) : (
                            <div className="w-full rounded-lg bg-amber-50 border border-amber-200 px-4 py-3 text-center text-sm text-amber-700">
                              Playing... Watch until the end to mark as watched
                            </div>
                          )}
                        </div>
                      ) : (
                        <div className="w-full h-52 rounded-lg border bg-muted flex items-center justify-center text-sm text-muted-foreground px-4 text-center">
                          No video link configured yet.
                        </div>
                      )}
                    </div>
                  </div>

                  <div className="rounded-xl border border-border p-4">
                    <div className="flex items-center gap-2 mb-2">
                      <FileBadge className="w-5 h-5 text-primary" />
                      <h3 className="font-semibold">Post-Orientation Assessment</h3>
                    </div>
                    <p className="text-sm text-muted-foreground mb-4">
                      Passing score: <strong>{orientationSettings.passing_score}%</strong>
                    </p>

                    <div className="space-y-5">
                      {normalizedQuestions.length === 0 && (
                        <div className="rounded-lg border bg-muted px-4 py-4 text-sm text-muted-foreground">
                          No assessment questions configured yet.
                        </div>
                      )}

                      {normalizedQuestions.map((question: any, index: number) => (
                        <div key={index} className="space-y-2">
                          <Label>{index + 1}. {question.question}</Label>
                          <Select
                            onValueChange={(value) => {
                              if (!assessmentSubmitted) {
                                setAssessmentAnswers((prev) => ({
                                  ...prev,
                                  [index]: value,
                                }));
                              }
                            }}
                            value={assessmentAnswers[index] ?? ''}
                            disabled={assessmentSubmitted}
                          >
                            <SelectTrigger className="rounded-lg">
                              <SelectValue placeholder="Select your answer" />
                            </SelectTrigger>
                            <SelectContent>
                              {question.choices.map((choice: string) => (
                                <SelectItem key={choice} value={choice}>
                                  {choice}
                                </SelectItem>
                              ))}
                            </SelectContent>
                          </Select>
                        </div>
                      ))}
                    </div>

                    {!assessmentSubmitted && normalizedQuestions.length > 0 && (
                      <div className="mt-6 rounded-lg border border-primary/20 bg-primary/5 p-4">
                        <p className="text-sm text-muted-foreground mb-4">
                          Answer all {normalizedQuestions.length} questions above, then click Submit Assessment to see your score.
                        </p>
                        <Button
                          onClick={submitAssessment}
                          disabled={!allQuestionsAnswered}
                          className="w-full bg-primary hover:bg-primary/90 text-white rounded-lg"
                        >
                          {allQuestionsAnswered ? 'Submit Assessment' : `Answer all questions (${Object.keys(assessmentAnswers).length}/${normalizedQuestions.length})`}
                        </Button>
                      </div>
                    )}

                    {assessmentSubmitted && (
                      <div className="mt-6 rounded-lg border px-4 py-4 text-sm">
                        <div className="flex items-center justify-between mb-2">
                          <span>Your Score</span>
                          <span className="font-semibold text-lg">{assessmentScore}%</span>
                        </div>
                        <div className="flex items-center justify-between">
                          <span>Result</span>
                          <span className={assessmentPassed ? 'text-green-600 font-semibold' : 'text-destructive font-semibold'}>
                            {assessmentPassed ? '✓ Passed' : '✗ Did not pass'}
                          </span>
                        </div>
                        {!assessmentPassed && (
                          <p className="text-xs text-muted-foreground mt-3">
                            You need {orientationSettings.passing_score}% to pass. Please review your answers.
                          </p>
                        )}
                      </div>
                    )}
                  </div>

                  <div className="rounded-xl border border-primary/20 bg-primary/5 p-4">
                    <h3 className="font-semibold mb-3">Orientation Checklist</h3>
                    <div className="space-y-2 text-sm">
                      <div className="flex items-center justify-between">
                        <span>Zoom Pre-Membership Orientation</span>
                        <span className={zoomClicked ? 'text-green-600 font-medium' : 'text-muted-foreground'}>
                          {zoomClicked ? '✓ Done' : 'Pending'}
                        </span>
                      </div>
                      <div className="flex items-center justify-between">
                        <span>Orientation Video</span>
                        <span className={videoInteracted ? 'text-green-600 font-medium' : 'text-muted-foreground'}>
                          {videoInteracted ? '✓ Done' : 'Pending'}
                        </span>
                      </div>
                      <div className="flex items-center justify-between">
                        <span>Assessment</span>
                        <span className={assessmentSubmitted && assessmentPassed ? 'text-green-600 font-medium' : 'text-muted-foreground'}>
                          {assessmentSubmitted && assessmentPassed ? '✓ Passed' : assessmentSubmitted ? '✗ Failed' : 'Pending'}
                        </span>
                      </div>
                      <div className="flex items-center justify-between">
                        <span>Orientation Certificate Generation</span>
                        <span className={zoomClicked && videoInteracted && assessmentSubmitted && assessmentPassed ? 'text-green-600 font-medium' : 'text-muted-foreground'}>
                          {zoomClicked && videoInteracted && assessmentSubmitted && assessmentPassed ? '✓ Ready' : 'Pending'}
                        </span>
                      </div>
                    </div>
                  </div>

                  {!orientationComplete && (
                    <div className="rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                      Submit Application will stay disabled until orientation is completed and the assessment passing score is met.
                    </div>
                  )}

                  {orientationComplete && (
                    <div className="rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800">
                      Orientation completed. You can now submit the application.
                    </div>
                  )}
                </CardContent>
              </Card>

              <div className="flex items-center justify-between">
                <Button type="button" variant="outline" onClick={() => setStep(3)} className="rounded-lg px-6 font-medium gap-2">
                  <ChevronLeft className="w-4 h-4" /> Back
                </Button>

                <Button
                  type="button"
                  disabled={!orientationComplete || saving}
                  onClick={submitFinalApplication}
                  className="bg-primary hover:bg-primary/90 text-white rounded-lg px-8 font-medium"
                >
                  {saving ? (
                    <>
                      <Loader2 className="w-4 h-4 mr-2 animate-spin" />
                      Submitting...
                    </>
                  ) : (
                    'Submit Application'
                  )}
                </Button>
              </div>
            </div>
          )}
        </div>
      </section>
    </div>
  );
}

function HeroSection({
  heroVisible,
  membershipTypeLabel,
}: {
  heroVisible: boolean;
  membershipTypeLabel: string;
}) {
  return (
    <section className="relative text-white py-24 sm:py-32 overflow-hidden">
      <div
        className="absolute inset-0 bg-[url('/src/images/bghd.jpg')] bg-cover bg-center"
        style={{ transition: 'transform 20s linear', transform: heroVisible ? 'scale(1)' : 'scale(1.05)' }}
      />
      <div className="absolute inset-0 bg-gradient-to-br from-black/70 to-black/70" />
      <div className="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="max-w-3xl">
          <Badge className="mb-4 bg-white/20 text-white border-white/30 backdrop-blur-sm">Application Form</Badge>
          <h1 className="text-5xl sm:text-6xl font-bold mb-4 leading-tight">Membership Application</h1>
          {membershipTypeLabel && (
            <p className="text-lg text-white/70 mb-2">
              Applying as: <span className="text-white font-semibold">{membershipTypeLabel}</span>
            </p>
          )}
          <p className="text-xl text-blue-100 leading-relaxed">
            Complete the form below to begin your journey with us.
          </p>
        </div>
      </div>
    </section>
  );
}