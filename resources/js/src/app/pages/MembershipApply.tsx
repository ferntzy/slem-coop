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
  ArrowRight,
  ChevronDown,
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

const MEMBERSHIP_AGE_REQUIREMENTS: Record<string, number> = {
  '1': 16,
  '2': 18,
};

const getMinimumAge = (membershipTypeId: string): number => {
  return MEMBERSHIP_AGE_REQUIREMENTS[membershipTypeId] ?? 18;
};

const calculateAge = (birthdate: string): number => {
  const today = new Date();
  const birth = new Date(birthdate);
  let age = today.getFullYear() - birth.getFullYear();
  const monthDiff = today.getMonth() - birth.getMonth();
  if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
    age--;
  }
  return age;
};

// ─── Max birthdate: today minus minimum age ───
const getMaxBirthdate = (membershipTypeId: string): string => {
  const minAge = getMinimumAge(membershipTypeId);
  const max = new Date();
  max.setFullYear(max.getFullYear() - minAge);
  return max.toISOString().split('T')[0];
};

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
  branch_id: string;
  remarks: string;
};

type BranchOption = {
  branch_id: number;
  name: string;
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

/* ─── Shared input helpers ─── */
// Strips non-digits and enforces max length
const digitsOnly = (value: string, maxLen = 999) =>
  value.replace(/[^0-9]/g, '').slice(0, maxLen);

// Strips non-digits, enforces 09 prefix, max 11 digits
const phMobileFormat = (value: string): string => {
  let digits = value.replace(/[^0-9]/g, '').slice(0, 11);
  if (digits.length >= 2 && !digits.startsWith('09')) {
    digits = '09' + digits.replace(/^0*9*/, '').slice(0, 9);
  }
  return digits;
};

/* ─── Particles ─── */
function Particles() {
  const colorClasses = [
    'bg-green-300 dark:bg-green-600',
    'bg-green-400 dark:bg-green-500',
    'bg-green-200 dark:bg-green-700',
    'bg-green-500 dark:bg-green-800',
  ];
  const particles = Array.from({ length: 24 }, (_, i) => ({
    id: i,
    size: Math.random() * 5 + 3,
    x: Math.random() * 100,
    delay: Math.random() * 8,
    duration: Math.random() * 10 + 12,
    opacity: Math.random() * 0.5 + 0.2,
    colorClass: colorClasses[Math.floor(Math.random() * colorClasses.length)],
  }));
  return (
    <div className="absolute inset-0 overflow-hidden pointer-events-none">
      {particles.map(p => (
        <div
          key={p.id}
          className={`absolute rounded-full ${p.colorClass}`}
          style={{
            width: p.size,
            height: p.size,
            left: `${p.x}%`,
            bottom: '-10px',
            opacity: p.opacity,
            animation: `floatUp ${p.duration}s ${p.delay}s infinite linear`,
          }}
        />
      ))}
    </div>
  );
}

/* ─── Section Divider ─── */
function SectionDivider({ label }: { label: string }) {
  return (
    <div className="flex items-center gap-3 pt-2">
      <div className="h-px flex-1 bg-green-200 dark:bg-green-900/50" />
      <span className="text-[10px] font-black uppercase tracking-widest text-green-600 dark:text-green-400">
        {label}
      </span>
      <div className="h-px flex-1 bg-green-200 dark:bg-green-900/50" />
    </div>
  );
}

/* ─── Step Indicator ─── */
function StepIndicator({ current }: { current: number }) {
  const steps = ['Personal', 'Application', 'Spouse & Co-Makers', 'Orientation'];
  const fullLabels = ['Personal Details', 'Application & Documents', 'Spouse & Co-Makers', 'Orientation'];

  return (
    <div className="mb-10 overflow-x-auto scrollbar-hide -mx-4 px-4">
      <div className="flex items-start min-w-max mx-auto w-full justify-center gap-0">
        {steps.map((label, i) => {
          const stepNum = i + 1;
          const done = stepNum < current;
          const active = stepNum === current;

          return (
            <div key={i} className="flex items-center">
              <div className="flex flex-col items-center w-16 sm:w-28">
                <div
                  className={`
                    w-10 h-10 rounded-full flex items-center justify-center font-black text-xs border-2 transition-all duration-300 flex-shrink-0
                    ${done ? 'bg-green-600 dark:bg-green-500 border-green-600 dark:border-green-500 text-white' : ''}
                    ${active ? 'bg-green-600 dark:bg-green-400 border-green-600 dark:border-green-400 text-white scale-110 shadow-lg shadow-green-500/30 dark:shadow-green-400/20' : ''}
                    ${!done && !active ? 'bg-white dark:bg-[#0d1410] border-green-200 dark:border-green-900 text-gray-400 dark:text-gray-600' : ''}
                  `}
                >
                  {done ? <Check className="w-4 h-4" /> : stepNum}
                </div>
                <span
                  className={`mt-2 text-[10px] sm:hidden font-black uppercase tracking-widest text-center leading-tight px-0.5
                    ${active ? 'text-green-600 dark:text-green-400' : 'text-gray-400 dark:text-gray-600'}`}
                >
                  {label}
                </span>
                <span
                  className={`mt-2 text-[10px] font-black uppercase tracking-widest text-center leading-tight hidden sm:block max-w-[100px]
                    ${active ? 'text-green-600 dark:text-green-400' : 'text-gray-400 dark:text-gray-600'}`}
                >
                  {fullLabels[i]}
                </span>
              </div>
              {i < steps.length - 1 && (
                <div
                  className={`w-6 sm:w-14 h-0.5 mb-6 flex-shrink-0 transition-all duration-500 ${done ? 'bg-green-600 dark:bg-green-500' : 'bg-green-100 dark:bg-green-900/40'}`}
                />
              )}
            </div>
          );
        })}
      </div>
    </div>
  );
}

/* ─── File Drop Zone ─── */
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
      <Label className="text-xs font-black uppercase tracking-widest text-gray-600 dark:text-gray-400">
        {label}
        {required && <span className="text-red-500 ml-0.5">*</span>}
      </Label>
      <div
        onDragOver={(e) => { e.preventDefault(); setDragging(true); }}
        onDragLeave={() => setDragging(false)}
        onDrop={(e) => { e.preventDefault(); setDragging(false); onChange(e.dataTransfer.files?.[0] ?? null); }}
        onClick={() => inputRef.current?.click()}
        className={`
          flex flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed
          cursor-pointer min-h-[100px] px-4 py-6 text-center transition-all duration-200
          ${dragging
            ? 'border-green-500 bg-green-50 dark:bg-green-900/20'
            : fileName
              ? 'border-green-400 dark:border-green-600 bg-green-50/50 dark:bg-green-900/10'
              : 'border-green-200 dark:border-green-900/60 bg-green-50/20 dark:bg-[#0d1410] hover:bg-green-50/50 dark:hover:bg-green-900/10'}
        `}
      >
        <Upload className={`w-5 h-5 ${fileName ? 'text-green-600 dark:text-green-400' : 'text-green-400 dark:text-green-600'}`} />
        {fileName ? (
          <span className="text-sm font-bold text-green-700 dark:text-green-400 break-all">{fileName}</span>
        ) : (
          <span className="text-sm text-gray-500 dark:text-gray-400 font-medium">
            Tap to <span className="text-green-600 dark:text-green-400 font-black">Browse</span>
          </span>
        )}
        {helperText && <span className="text-xs text-gray-400 dark:text-gray-600">{helperText}</span>}
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

/* ─── Membership Type Card ─── */
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
        relative w-full text-left rounded-2xl border-2 p-4 transition-all duration-200 cursor-pointer
        ${selected
          ? 'border-green-500 dark:border-green-400 bg-green-50/50 dark:bg-green-900/20 shadow-lg shadow-green-500/10'
          : 'border-green-100 dark:border-green-900/40 bg-white dark:bg-[#111b17] hover:border-green-300 dark:hover:border-green-700'}
      `}
    >
      <div className={`absolute top-4 right-4 w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all ${selected ? 'border-green-500 dark:border-green-400 bg-green-500 dark:bg-green-400' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-transparent'}`}>
        {selected && <div className="w-2 h-2 rounded-full bg-white" />}
      </div>
      <div className="flex items-start gap-3 pr-8">
        <div className={`w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 transition-colors ${selected ? 'bg-green-600 dark:bg-green-500 text-white' : 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400'}`}>
          <Icon className="w-4 h-4" />
        </div>
        <div>
          <p className={`font-black text-sm uppercase tracking-wide ${selected ? 'text-green-700 dark:text-green-400' : 'text-gray-700 dark:text-gray-300'}`}>{type.label}</p>
          <p className="text-xs text-gray-500 dark:text-gray-400 mt-0.5 leading-relaxed font-medium">{type.description}</p>
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
  const [branches, setBranches] = useState<BranchOption[]>([]);
  const [resolvedBranch, setResolvedBranch] = useState<BranchOption | null>(null);

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
        request.onerror = () => { console.error('Failed to get file from IndexedDB:', request.error); resolve(null); };
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
    formState: { errors: err2 },
    setValue: set2,
    watch: watch2,
  } = useForm<ApplicationData>({
    defaultValues: { application_date: new Date().toISOString().split('T')[0], remarks: '', membership_type_id: selectedTypeId, branch_id: '' },
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
      try { rawQuestions = JSON.parse(rawQuestions); } catch { rawQuestions = []; }
    }
    if (!Array.isArray(rawQuestions)) rawQuestions = [];
    return rawQuestions.map((q: any) => {
      let rawChoices = q?.choices ?? [];
      if (typeof rawChoices === 'string') {
        try { rawChoices = JSON.parse(rawChoices); } catch { rawChoices = []; }
      }
      if (!Array.isArray(rawChoices)) rawChoices = [];
      return { ...q, choices: rawChoices.map((choice: any) => typeof choice === 'string' ? choice : choice?.value ?? '') };
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
  const allQuestionsAnswered = totalQuestions > 0 && normalizedQuestions.every((_: any, idx: number) => assessmentAnswers[idx] !== undefined && assessmentAnswers[idx] !== '');

  const orientationComplete = useMemo(() => {
    if (!orientationSettings.require_for_loan) return true;
    return zoomClicked && videoInteracted && assessmentSubmitted && assessmentPassed;
  }, [zoomClicked, videoInteracted, assessmentSubmitted, assessmentPassed, orientationSettings.require_for_loan]);

  useEffect(() => {
    setOrientationProgress((prev) => ({
      ...prev,
      zoom_attended: zoomClicked,
      video_completed: videoInteracted,
      assessment_passed: assessmentSubmitted && assessmentPassed,
      certificate_generated: zoomClicked && videoInteracted && assessmentSubmitted && assessmentPassed,
    }));
  }, [zoomClicked, videoInteracted, assessmentSubmitted, assessmentPassed]);

  useEffect(() => {
    const t = setTimeout(() => setHeroVisible(true), 100);
    return () => clearTimeout(t);
  }, []);

  useEffect(() => {
    fetch('/api/branches')
      .then((res) => res.json())
      .then((data) => {
        const options = Array.isArray(data) ? data : [];
        setBranches(options);
        if (options.length === 1) {
          set2('branch_id', String(options[0].branch_id));
        }
      })
      .catch(() => {
        toast.error('Failed to load branch options.');
      });

    fetch('/api/orientation-settings')
      .then((res) => res.json())
      .then((data) => {
        setOrientationSettings({
          zoom_link: data?.zoom_link ?? '',
          video_link: data?.video_link ?? '',
          passing_score: Number(data?.passing_score ?? 75),
          require_for_loan: Boolean(data?.require_for_loan ?? true),
          questions: Array.isArray(data?.questions) || typeof data?.questions === 'string' ? data.questions : [],
        });
      })
      .catch(() => { toast.error('Failed to load orientation settings.'); });

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
        videoId,
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
      if (draft.selectedTypeId && MEMBERSHIP_TYPE_LABELS[draft.selectedTypeId]) setSelectedTypeId(draft.selectedTypeId);
      if (draft.profileData) {
        setProfileData(draft.profileData);
        Object.entries(draft.profileData).forEach(([key, value]) => set1(key as keyof ProfileData, value ?? ''));
      }
      if (draft.applicationData) {
        setApplicationData(draft.applicationData);
        set2('application_date', draft.applicationData.application_date ?? new Date().toISOString().split('T')[0]);
        set2('remarks', draft.applicationData.remarks ?? '');
        set2('branch_id', draft.applicationData.branch_id ?? '');
        set2('membership_type_id', draft.applicationData.membership_type_id ?? draft.selectedTypeId ?? selectedTypeId);
      }
      if (draft.spouseData) {
        setSpouseData(draft.spouseData);
        Object.entries(draft.spouseData).forEach(([key, value]) => set3(key as keyof SpouseData, value ?? ''));
      }
      if (draft.coMakersData && draft.coMakersData.length > 0) setCoMakersData(draft.coMakersData);
      if (draft.orientationProgress) setOrientationProgress(draft.orientationProgress);
      setStep(draft.step ?? 1);
      if (draft.idFileName) {
        getFileFromDb('id_file_front').then((file) => { if (file) setIdFileFront(file); });
        getFileFromDb('id_file_back').then((file) => { if (file) setIdFileBack(file); });
      }
    } catch {
      console.error('Failed to restore draft');
    }
  }, [set1, set2, set3, selectedTypeId, getFileFromDb]);

  useEffect(() => {
    const timeout = setTimeout(async () => {
      const currentProfile = { ...watchedProfile, ...profileData };
      const currentApplication = { ...watchedApplication, ...applicationData };
      const currentSpouse = watch3();
      const draft: DraftData = {
        step, selectedTypeId,
        profileData: currentProfile as ProfileData,
        applicationData: currentApplication as ApplicationData,
        spouseData: (Object.values(currentSpouse).some(v => v) ? currentSpouse : null) as SpouseData | null,
        coMakersData, orientationProgress,
        idFileName: (idFileFront?.name || idFileBack?.name) ?? null,
      };
      localStorage.setItem(MEMBERSHIP_DRAFT_KEY, JSON.stringify(draft));
      if (idFileFront) await saveFileToDb('id_file_front', idFileFront);
      if (idFileBack) await saveFileToDb('id_file_back', idFileBack);
    }, 500);
    return () => clearTimeout(timeout);
  }, [step, selectedTypeId, profileData, applicationData, spouseData, coMakersData, orientationProgress, watchedProfile, watchedApplication, watch3, idFileFront, idFileBack, saveFileToDb]);

  // Watch municipality and resolve branch automatically
  useEffect(() => {
    const municipality = watchedProfile?.municipality;
    if (!municipality || municipality.trim() === '') {
      setResolvedBranch(null);
      return;
    }

    fetch(`/api/resolve-branch-by-municipality?municipality=${encodeURIComponent(municipality)}`)
      .then((res) => res.json())
      .then((data) => {
        if (data.branch_id && data.name) {
          setResolvedBranch({ branch_id: data.branch_id, name: data.name });
          // Auto-set the branch_id in form2
          set2('branch_id', String(data.branch_id));
        } else {
          setResolvedBranch(null);
        }
      })
      .catch(() => {
        setResolvedBranch(null);
      });
  }, [watchedProfile?.municipality, set2]);

  const submitProfile = (data: ProfileData) => {
    setProfileData(data);
    toast.success('Personal details saved.');
    setStep(2);
  };

  const saveApplicationStep = (data: ApplicationData) => {
    const finalData = { ...data, membership_type_id: selectedTypeId };
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
    if (!profileData) { toast.error('Personal data missing. Please go back to step 1.'); return; }
    if (!applicationData) { toast.error('Application details missing. Please go back to step 2.'); return; }
    if (!profileData.municipality) { toast.error('Municipality is required for branch assignment. Please go back to step 1.'); return; }
    if (!applicationData.branch_id) { toast.error('Branch could not be assigned for this municipality. Please contact support.'); return; }
    if (!orientationComplete) { toast.error('Please complete the orientation first.'); return; }
    if (!idFileFront || !idFileBack) { toast.error('Please upload both front and back of your ID.'); return; }

    setSaving(true);
    try {
      const formData = new FormData();
      Object.entries(profileData).forEach(([k, v]) => { if (v && String(v).trim()) formData.append(k, String(v)); });
      formData.append('membership_type_id', selectedTypeId);
      formData.append('application_date', applicationData.application_date);
      formData.append('branch_id', applicationData.branch_id);
      if (applicationData.remarks && applicationData.remarks.trim()) formData.append('remarks', applicationData.remarks);
      if (spouseData && spouseData.full_name && spouseData.full_name.trim()) {
        Object.entries(spouseData).forEach(([k, v]) => { if (v && String(v).trim()) formData.append(`spouse_${k}`, String(v)); });
      }
      const validCoMakers = coMakersData.filter(cm => cm.full_name && cm.full_name.trim());
      if (validCoMakers.length > 0) formData.append('co_makers', JSON.stringify(validCoMakers));
      formData.append('orientation_zoom_attended', String(orientationProgress.zoom_attended));
      formData.append('orientation_video_completed', String(orientationProgress.video_completed));
      formData.append('orientation_assessment_passed', String(orientationProgress.assessment_passed));
      formData.append('orientation_certificate_generated', String(orientationProgress.certificate_generated));
      if (assessmentScore) formData.append('orientation_score', String(assessmentScore));
      if (idFileFront) formData.append('id_document_front', idFileFront);
      if (idFileBack) formData.append('id_document_back', idFileBack);

      const res = await fetch('/api/membership-application', {
        method: 'POST',
        headers: { Accept: 'application/json' },
        body: formData,
      });
      const json = await res.json();
      if (!res.ok) { toast.error(`${json.message}${json.error ? '\n' + json.error : ''}`); return; }

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
    setSubmitted(false); setStep(1); setProfileData(null); setApplicationData(null);
    setSpouseData(null); setCoMakersData([]); setOrientationProgress(emptyOrientationProgress);
    setAssessmentAnswers({}); setZoomClicked(false); setVideoInteracted(false);
    setAssessmentSubmitted(false); setIdFileFront(null); setIdFileBack(null);
    clearFileFromDb('id_file_front'); clearFileFromDb('id_file_back');
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

  const labelClass = 'text-xs font-black uppercase tracking-widest text-gray-600 dark:text-gray-400';
  const inputClass = 'rounded-xl border-green-200 dark:border-green-900/50 bg-white dark:bg-[#0d1410] focus:border-green-500 dark:focus:border-green-400 focus:ring-green-500/20';
  const cardClass = 'rounded-[2rem] border border-green-100 dark:border-green-900/30 bg-white dark:bg-[#111b17] shadow-sm';
  const cardHeaderClass = 'bg-gradient-to-r from-green-600 to-green-700 dark:from-green-900/60 dark:to-green-800/40 rounded-t-[1.9rem] p-6';
  const navButtonClass = 'rounded-full border-2 border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/20 font-black uppercase tracking-widest text-xs px-8 py-3 gap-2';
  const primaryButtonClass = 'rounded-full bg-green-600 hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-400 text-white font-black uppercase tracking-widest text-xs px-8 py-3 shadow-lg shadow-green-500/20';

  if (submitted) {
    return (
      <div className="flex flex-col bg-white dark:bg-[#0a0f0c] text-gray-900 dark:text-white transition-colors duration-500">
        <HeroSection heroVisible={heroVisible} membershipTypeLabel={membershipTypeLabel} />
        <section className="py-24 flex items-center justify-center px-4 bg-green-50/30 dark:bg-[#0d1410]">
          <Card className="max-w-md w-full rounded-[2rem] border border-green-100 dark:border-green-900/30 shadow-2xl bg-white dark:bg-[#111b17] text-center overflow-hidden">
            <div className="bg-gradient-to-br from-green-600 to-green-800 dark:from-green-900/60 dark:to-green-800/40 p-10 flex flex-col items-center">
              <div className="w-16 h-16 rounded-2xl bg-white/20 flex items-center justify-center mb-4 shadow-lg">
                <Check className="w-8 h-8 text-white" />
              </div>
              <h2 className="text-2xl font-black text-white uppercase tracking-tight">Application Submitted!</h2>
            </div>
            <CardContent className="pt-8 pb-10 space-y-4 px-8">
              <p className="text-gray-600 dark:text-gray-400 font-medium leading-relaxed">
                Thank you for applying as a{' '}
                <strong className="text-green-700 dark:text-green-400 font-black">{membershipTypeLabel}</strong>.
                We will review your application and contact you within 5–7 business days.
              </p>
              <button
                onClick={resetForm}
                className={`mt-4 w-full inline-flex items-center justify-center ${primaryButtonClass}`}
              >
                Submit Another Application
              </button>
            </CardContent>
          </Card>
        </section>
      </div>
    );
  }

  if (!initialTypeId || !MEMBERSHIP_TYPE_LABELS[initialTypeId]) return null;

  return (
    <>
      <style>{`
        @keyframes floatUp { 0% { transform: translateY(0) scale(1); opacity: 0.2; } 100% { transform: translateY(-100vh) scale(0.5); opacity: 0; } }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
      `}</style>

      <div className="flex flex-col bg-white dark:bg-[#0a0f0c] text-gray-900 dark:text-white transition-colors duration-500">
        <HeroSection heroVisible={heroVisible} membershipTypeLabel={membershipTypeLabel} />

        <section className="relative py-16 sm:py-24 overflow-hidden bg-green-50/30 dark:bg-[#0d1410]">
          <div className="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            <div className="flex flex-col sm:flex-row items-center justify-center gap-3 mb-8">
              <div className="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-green-100/60 dark:bg-white/10 border border-green-300 dark:border-white/20 backdrop-blur-md">
                <div className="w-2 h-2 bg-green-600 dark:bg-green-400 rounded-full animate-pulse" />
                <span className="text-xs font-black text-green-900 dark:text-white/90 uppercase tracking-widest">
                  Applying as: {membershipTypeLabel}
                </span>
              </div>
              <button
                type="button"
                onClick={clearDraft}
                className="text-xs font-black uppercase tracking-widest text-gray-400 dark:text-gray-600 hover:text-red-500 dark:hover:text-red-400 transition-colors border border-gray-200 dark:border-gray-800 rounded-full px-4 py-2"
              >
                Clear Saved Draft
              </button>
            </div>

            <StepIndicator current={step} />

            {/* ── STEP 1 ── */}
            {step === 1 && (
              <form onSubmit={handle1(submitProfile)}>
                <Card className={cardClass}>
                  <div className={cardHeaderClass}>
                    <div className="flex items-center gap-3">
                      <div className="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center text-white font-black text-sm flex-shrink-0">1</div>
                      <div>
                        <p className="text-xs font-black text-white/70 uppercase tracking-widest">Step 1</p>
                        <h3 className="text-lg font-black text-white uppercase tracking-tight">Personal Details</h3>
                      </div>
                    </div>
                    <p className="text-sm text-white/70 mt-2 font-medium">Fill in your personal information accurately.</p>
                  </div>

                  <CardContent className="space-y-6 p-6 sm:p-8">
                    <SectionDivider label="Basic Information" />

                    <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                      <div className="space-y-1.5">
                        <Label className={labelClass}>First name <span className="text-red-500">*</span></Label>
                        <Input {...reg1('first_name', { required: 'Required' })} className={inputClass} />
                        {err1.first_name && <p className="text-xs text-red-500 font-medium">{err1.first_name.message}</p>}
                      </div>
                      <div className="space-y-1.5">
                        <Label className={labelClass}>Middle name</Label>
                        <Input {...reg1('middle_name')} className={inputClass} />
                      </div>
                      <div className="space-y-1.5">
                        <Label className={labelClass}>Last name <span className="text-red-500">*</span></Label>
                        <Input {...reg1('last_name', { required: 'Required' })} className={inputClass} />
                        {err1.last_name && <p className="text-xs text-red-500 font-medium">{err1.last_name.message}</p>}
                      </div>
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                      <div className="space-y-1.5">
                        <Label className={labelClass}>Email <span className="text-red-500">*</span></Label>
                        <Input type="email" {...reg1('email', { required: 'Required' })} className={inputClass} />
                        {err1.email && <p className="text-xs text-red-500 font-medium">{err1.email.message}</p>}
                      </div>

                      {/* ── Mobile Number (PH format) ── */}
                      <div className="space-y-1.5">
                        <Label className={labelClass}>Mobile number <span className="text-red-500">*</span></Label>
                        <div className="flex items-center rounded-xl border border-green-200 dark:border-green-900/50 bg-white dark:bg-[#0d1410] overflow-hidden focus-within:border-green-500 dark:focus-within:border-green-400 focus-within:ring-2 focus-within:ring-green-500/20">
                         {/* <span className="px-3 text-sm font-black text-gray-500 dark:text-gray-400 border-r border-green-200 dark:border-green-900/50 select-none">+63</span> */}
                          <Input
                            inputMode="numeric"
                            placeholder="09XXXXXXXXX"
                            maxLength={11}
                            className="border-0 shadow-none focus-visible:ring-0 rounded-none bg-transparent"
                            {...reg1('mobile_number', {
                              required: 'Required',
                              pattern: { value: /^09\d{9}$/, message: 'Must be a valid PH number starting with 09 (e.g. 09123456789)' },
                            })}
                            onInput={(e) => {
                              const input = e.currentTarget;
                              input.value = phMobileFormat(input.value);
                              set1('mobile_number', input.value, { shouldValidate: true });
                            }}
                            onPaste={(e) => e.preventDefault()}
                            onKeyDown={(e) => { if (!/[0-9]|Backspace|Delete|ArrowLeft|ArrowRight|Tab/.test(e.key)) e.preventDefault(); }}
                          />
                        </div>
                        {err1.mobile_number && <p className="text-xs text-red-500 font-medium">{err1.mobile_number.message}</p>}
                      </div>
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                      {/* ── Birthdate — max capped at minAge years ago ── */}
                      <div className="space-y-1.5">
                        <Label className={labelClass}>
                          Birthdate <span className="text-red-500">*</span>
                          <span className="ml-1 text-gray-400 normal-case font-medium">(must be {getMinimumAge(selectedTypeId)}+ years old)</span>
                        </Label>
                        <Input
                          type="date"
                          max={getMaxBirthdate(selectedTypeId)}
                          {...reg1('birthdate', {
                            required: 'Birthdate is required',
                            validate: {
                              notFuture: (value) => {
                                const today = new Date().toISOString().split('T')[0];
                                return value < today || 'Birthdate cannot be today or in the future';
                              },
                              ageRequirement: (value) => {
                                const minAge = getMinimumAge(selectedTypeId);
                                const age = calculateAge(value);
                                return age >= minAge || `You must be at least ${minAge} years old (your age: ${age})`;
                              },
                            },
                          })}
                          className={inputClass}
                        />
                        {err1.birthdate && <p className="text-xs text-red-500 font-medium">{err1.birthdate.message}</p>}
                      </div>

                      <div className="space-y-1.5">
                        <Label className={labelClass}>Sex <span className="text-red-500">*</span></Label>
                        <input type="hidden" {...reg1('sex', { required: 'Required' })} />
                        <Select value={sex || ''} onValueChange={(v) => set1('sex', v, { shouldValidate: true, shouldDirty: true })}>
                          <SelectTrigger className={inputClass}><SelectValue placeholder="Select" /></SelectTrigger>
                          <SelectContent>
                            <SelectItem value="Male">Male</SelectItem>
                            <SelectItem value="Female">Female</SelectItem>
                          </SelectContent>
                        </Select>
                        {err1.sex && <p className="text-xs text-red-500 font-medium">{err1.sex.message}</p>}
                      </div>
                      <div className="space-y-1.5">
                        <Label className={labelClass}>Civil status <span className="text-red-500">*</span></Label>
                        <input type="hidden" {...reg1('civil_status', { required: 'Required' })} />
                        <Select value={civilStatus || ''} onValueChange={(v) => set1('civil_status', v, { shouldValidate: true, shouldDirty: true })}>
                          <SelectTrigger className={inputClass}><SelectValue placeholder="Select" /></SelectTrigger>
                          <SelectContent>
                            <SelectItem value="Single">Single</SelectItem>
                            <SelectItem value="Married">Married</SelectItem>
                            <SelectItem value="Widowed">Widowed</SelectItem>
                            <SelectItem value="Separated">Separated</SelectItem>
                            <SelectItem value="Annulled">Annulled</SelectItem>
                          </SelectContent>
                        </Select>
                        {err1.civil_status && <p className="text-xs text-red-500 font-medium">{err1.civil_status.message}</p>}
                      </div>
                    </div>

                    <SectionDivider label="Identification" />

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                      <div className="space-y-1.5">
                        <Label className={labelClass}>ID Type <span className="text-red-500">*</span></Label>
                        <input type="hidden" {...reg1('id_type', { required: 'Required' })} />
                        <Select value={idType || ''} onValueChange={(v) => set1('id_type', v, { shouldValidate: true, shouldDirty: true })}>
                          <SelectTrigger className={inputClass}><SelectValue placeholder="Select ID type" /></SelectTrigger>
                          <SelectContent>
                            {ID_TYPES.map((t) => <SelectItem key={t} value={t}>{t}</SelectItem>)}
                          </SelectContent>
                        </Select>
                        {err1.id_type && <p className="text-xs text-red-500 font-medium">{err1.id_type.message}</p>}
                      </div>

                      {/* ── ID Number — digits only ── */}
                      <div className="space-y-1.5">
                        <Label className={labelClass}>ID Number <span className="text-red-500">*</span></Label>
                        <Input
                          inputMode="numeric"
                          placeholder="Enter ID number"
                          {...reg1('id_number', {
                            required: 'Required',
                            pattern: { value: /^[0-9]+$/, message: 'ID number must contain digits only' },
                          })}
                          className={inputClass}
                          onInput={(e) => {
                            const input = e.currentTarget;
                            input.value = digitsOnly(input.value);
                            set1('id_number', input.value, { shouldValidate: true });
                          }}
                          onPaste={(e) => e.preventDefault()}
                          onKeyDown={(e) => { if (!/[0-9]|Backspace|Delete|ArrowLeft|ArrowRight|Tab/.test(e.key)) e.preventDefault(); }}
                        />
                        {err1.id_number && <p className="text-xs text-red-500 font-medium">{err1.id_number.message}</p>}
                      </div>
                    </div>

                    <SectionDivider label="Address" />

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                      <div className="space-y-1.5">
                        <Label className={labelClass}>House No. <span className="text-red-500">*</span></Label>
                        <Input {...reg1('house_no', { required: 'Required' })} className={inputClass} />
                        {err1.house_no && <p className="text-xs text-red-500 font-medium">{err1.house_no.message}</p>}
                      </div>
                      <div className="space-y-1.5">
                        <Label className={labelClass}>Street / Barangay <span className="text-red-500">*</span></Label>
                        <Input {...reg1('street_barangay', { required: 'Required' })} className={inputClass} />
                        {err1.street_barangay && <p className="text-xs text-red-500 font-medium">{err1.street_barangay.message}</p>}
                      </div>
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                      <div className="space-y-1.5">
                        <Label className={labelClass}>Municipality / City <span className="text-red-500">*</span></Label>
                        <Input {...reg1('municipality', { required: 'Required' })} className={inputClass} />
                        {err1.municipality && <p className="text-xs text-red-500 font-medium">{err1.municipality.message}</p>}
                      </div>
                      <div className="space-y-1.5">
                        <Label className={labelClass}>Province <span className="text-red-500">*</span></Label>
                        <Input {...reg1('province', { required: 'Required' })} className={inputClass} />
                        {err1.province && <p className="text-xs text-red-500 font-medium">{err1.province.message}</p>}
                      </div>
                      <div className="space-y-1.5">
                        <Label className={labelClass}>Zip code <span className="text-red-500">*</span></Label>
                        <Input
                          inputMode="numeric"
                          maxLength={4}
                          {...reg1('zip_code', { required: 'Required', pattern: { value: /^\d{4}$/, message: 'Must be a 4-digit zip code' } })}
                          className={inputClass}
                          onInput={(e) => {
                            const input = e.currentTarget;
                            input.value = digitsOnly(input.value, 4);
                            set1('zip_code', input.value, { shouldValidate: true });
                          }}
                          onPaste={(e) => e.preventDefault()}
                          onKeyDown={(e) => { if (!/[0-9]|Backspace|Delete|ArrowLeft|ArrowRight|Tab/.test(e.key)) e.preventDefault(); }}
                        />
                        {err1.zip_code && <p className="text-xs text-red-500 font-medium">{err1.zip_code.message}</p>}
                      </div>
                    </div>

                    <SectionDivider label="Employment & Income" />

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                      <div className="space-y-1.5">
                        <Label className={labelClass}>Occupation <span className="text-red-500">*</span></Label>
                        <Input {...reg1('occupation', { required: 'Required' })} className={inputClass} />
                        {err1.occupation && <p className="text-xs text-red-500 font-medium">{err1.occupation.message}</p>}
                      </div>
                      <div className="space-y-1.5">
                        <Label className={labelClass}>Employer / Business Name</Label>
                        <Input {...reg1('employer_name')} className={inputClass} />
                      </div>
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                      <div className="space-y-1.5">
                        <Label className={labelClass}>Source of Income <span className="text-red-500">*</span></Label>
                        <input type="hidden" {...reg1('source_of_income', { required: 'Required' })} />
                        <Select value={sourceOfIncome || ''} onValueChange={(v) => set1('source_of_income', v, { shouldValidate: true, shouldDirty: true })}>
                          <SelectTrigger className={inputClass}><SelectValue placeholder="Select source" /></SelectTrigger>
                          <SelectContent>
                            {SOURCE_OF_INCOME_OPTIONS.map((s) => <SelectItem key={s} value={s}>{s}</SelectItem>)}
                          </SelectContent>
                        </Select>
                        {err1.source_of_income && <p className="text-xs text-red-500 font-medium">{err1.source_of_income.message}</p>}
                      </div>
                      <div className="space-y-1.5">
                        <Label className={labelClass}>Monthly Income Range <span className="text-red-500">*</span></Label>
                        <input type="hidden" {...reg1('monthly_income_range', { required: 'Required' })} />
                        <Select value={monthlyIncomeRange || ''} onValueChange={(v) => set1('monthly_income_range', v, { shouldValidate: true, shouldDirty: true })}>
                          <SelectTrigger className={inputClass}><SelectValue placeholder="Select range" /></SelectTrigger>
                          <SelectContent>
                            {MONTHLY_INCOME_RANGES.map((r) => <SelectItem key={r} value={r}>{r}</SelectItem>)}
                          </SelectContent>
                        </Select>
                        {err1.monthly_income_range && <p className="text-xs text-red-500 font-medium">{err1.monthly_income_range.message}</p>}
                      </div>
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                      {/* ── Monthly Income — digits only ── */}
                      <div className="space-y-1.5">
                        <Label className={labelClass}>Monthly Income (₱) <span className="text-red-500">*</span></Label>
                        <div className="flex items-center rounded-xl border border-green-200 dark:border-green-900/50 bg-white dark:bg-[#0d1410] overflow-hidden focus-within:border-green-500 dark:focus-within:border-green-400 focus-within:ring-2 focus-within:ring-green-500/20">
                          <span className="px-3 text-sm font-black text-gray-500 dark:text-gray-400 border-r border-green-200 dark:border-green-900/50 select-none">₱</span>
                          <Input
                            inputMode="numeric"
                            placeholder="0"
                            className="border-0 shadow-none focus-visible:ring-0 rounded-none bg-transparent"
                            {...reg1('monthly_income', {
                              required: 'Required',
                              pattern: { value: /^[0-9]+$/, message: 'Must be a valid amount' },
                            })}
                            onInput={(e) => {
                              const input = e.currentTarget;
                              input.value = digitsOnly(input.value);
                              set1('monthly_income', input.value, { shouldValidate: true });
                            }}
                            onPaste={(e) => e.preventDefault()}
                            onKeyDown={(e) => { if (!/[0-9]|Backspace|Delete|ArrowLeft|ArrowRight|Tab/.test(e.key)) e.preventDefault(); }}
                          />
                        </div>
                        {err1.monthly_income && <p className="text-xs text-red-500 font-medium">{err1.monthly_income.message}</p>}
                      </div>
                      {sourceOfIncome === 'Business' && (
                        <div className="space-y-1.5">
                          <Label className={labelClass}>Years in Business</Label>
                          <Input
                            inputMode="numeric"
                            {...reg1('years_in_business')}
                            className={inputClass}
                            onInput={(e) => {
                              const input = e.currentTarget;
                              input.value = digitsOnly(input.value);
                              set1('years_in_business', input.value);
                            }}
                            onPaste={(e) => e.preventDefault()}
                            onKeyDown={(e) => { if (!/[0-9]|Backspace|Delete|ArrowLeft|ArrowRight|Tab/.test(e.key)) e.preventDefault(); }}
                          />
                        </div>
                      )}
                    </div>

                    <SectionDivider label="Family & Dependents" />

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                      <div className="space-y-1.5">
                        <Label className={labelClass}>Number of Dependents</Label>
                        <Input
                          inputMode="numeric"
                          {...reg1('dependents_count')}
                          className={inputClass}
                          onInput={(e) => {
                            const input = e.currentTarget;
                            input.value = digitsOnly(input.value);
                            set1('dependents_count', input.value);
                          }}
                          onPaste={(e) => e.preventDefault()}
                          onKeyDown={(e) => { if (!/[0-9]|Backspace|Delete|ArrowLeft|ArrowRight|Tab/.test(e.key)) e.preventDefault(); }}
                        />
                      </div>
                      <div className="space-y-1.5">
                        <Label className={labelClass}>Children Currently in School</Label>
                        <Input
                          inputMode="numeric"
                          {...reg1('children_in_school_count')}
                          className={inputClass}
                          onInput={(e) => {
                            const input = e.currentTarget;
                            input.value = digitsOnly(input.value);
                            set1('children_in_school_count', input.value);
                          }}
                          onPaste={(e) => e.preventDefault()}
                          onKeyDown={(e) => { if (!/[0-9]|Backspace|Delete|ArrowLeft|ArrowRight|Tab/.test(e.key)) e.preventDefault(); }}
                        />
                      </div>
                    </div>

                    <SectionDivider label="Emergency Contact" />

                    <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                      <div className="space-y-1.5">
                        <Label className={labelClass}>Full Name <span className="text-red-500">*</span></Label>
                        <Input {...reg1('emergency_full_name', { required: 'Required' })} className={inputClass} />
                        {err1.emergency_full_name && <p className="text-xs text-red-500 font-medium">{err1.emergency_full_name.message}</p>}
                      </div>

                      {/* ── Emergency Phone (PH format) ── */}
                      <div className="space-y-1.5">
                        <Label className={labelClass}>Phone Number <span className="text-red-500">*</span></Label>
                        <div className="flex items-center rounded-xl border border-green-200 dark:border-green-900/50 bg-white dark:bg-[#0d1410] overflow-hidden focus-within:border-green-500 dark:focus-within:border-green-400 focus-within:ring-2 focus-within:ring-green-500/20">
                          {/* <span className="px-3 text-sm font-black text-gray-500 dark:text-gray-400 border-r border-green-200 dark:border-green-900/50 select-none">+63</span> */}
                          <Input
                            inputMode="numeric"
                            placeholder="09XXXXXXXXX"
                            maxLength={11}
                            className="border-0 shadow-none focus-visible:ring-0 rounded-none bg-transparent"
                            {...reg1('emergency_phone', {
                              required: 'Required',
                              pattern: { value: /^09\d{9}$/, message: 'Must be a valid PH number starting with 09' },
                            })}
                            onInput={(e) => {
                              const input = e.currentTarget;
                              input.value = phMobileFormat(input.value);
                              set1('emergency_phone', input.value, { shouldValidate: true });
                            }}
                            onPaste={(e) => e.preventDefault()}
                            onKeyDown={(e) => { if (!/[0-9]|Backspace|Delete|ArrowLeft|ArrowRight|Tab/.test(e.key)) e.preventDefault(); }}
                          />
                        </div>
                        {err1.emergency_phone && <p className="text-xs text-red-500 font-medium">{err1.emergency_phone.message}</p>}
                      </div>

                      <div className="space-y-1.5">
                        <Label className={labelClass}>Relationship <span className="text-red-500">*</span></Label>
                        <Input {...reg1('emergency_relationship', { required: 'Required' })} className={inputClass} />
                        {err1.emergency_relationship && <p className="text-xs text-red-500 font-medium">{err1.emergency_relationship.message}</p>}
                      </div>
                    </div>
                  </CardContent>
                </Card>

                <div className="flex justify-end mt-6">
                  <button type="submit" className={`w-full sm:w-auto inline-flex items-center justify-center ${primaryButtonClass}`}>
                    Save & Continue <ArrowRight className="ml-2 w-4 h-4" />
                  </button>
                </div>
              </form>
            )}

            {/* ── STEP 2 ── */}
            {step === 2 && (
              <form onSubmit={handle2(saveApplicationStep)}>
                <div className="flex flex-col lg:flex-row gap-6">
                  <div className="lg:w-72 xl:w-80 order-first lg:order-last">
                    <Card className={cardClass}>
                      <div className={cardHeaderClass}>
                        <h3 className="text-base font-black text-white uppercase tracking-tight">Documents</h3>
                        <p className="text-sm text-white/70 mt-1 font-medium">Upload your ID (front and back).</p>
                      </div>
                      <CardContent className="space-y-5 p-6">
                        {profileData?.id_type && (
                          <div className="rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 p-3">
                            <p className="text-xs font-black uppercase tracking-widest text-green-700 dark:text-green-400 mb-0.5">ID Type Selected</p>
                            <p className="text-sm font-bold text-green-800 dark:text-green-300">{profileData.id_type}</p>
                          </div>
                        )}
                        <FileDropZone label="ID Front" required inputRef={idFrontRef} fileName={idFileFront?.name} onChange={setIdFileFront} helperText="Front side of your ID" />
                        <FileDropZone label="ID Back" required inputRef={idBackRef} fileName={idFileBack?.name} onChange={setIdFileBack} helperText="Back side of your ID" />
                      </CardContent>
                    </Card>
                  </div>

                  <div className="flex-1">
                    <Card className={cardClass}>
                      <div className={cardHeaderClass}>
                        <div className="flex items-center gap-3">
                          <div className="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center text-white font-black text-sm flex-shrink-0">2</div>
                          <div>
                            <p className="text-xs font-black text-white/70 uppercase tracking-widest">Step 2</p>
                            <h3 className="text-lg font-black text-white uppercase tracking-tight">Application Details</h3>
                          </div>
                        </div>
                        <p className="text-sm text-white/70 mt-2 font-medium">Review and save before orientation.</p>
                      </div>

                      <CardContent className="space-y-6 p-6 sm:p-8">
                        <div className="space-y-2">
                          <p className="text-xs font-black uppercase tracking-widest text-gray-600 dark:text-gray-400 mb-3">Membership Type</p>
                          {MEMBERSHIP_TYPES.filter(type => type.id === selectedTypeId).map((type) => (
                            <MembershipTypeCard key={type.id} type={type} selected onSelect={() => {}} />
                          ))}
                        </div>

                        <div className="space-y-1.5">
                          <Label className={labelClass}>Application Date</Label>
                          <Input type="date" {...reg2('application_date')} className={`${inputClass} opacity-60 cursor-not-allowed`} readOnly />
                        </div>

                        <div className="space-y-1.5">
                          <Label className={labelClass}>Branch <span className="text-red-500">*</span></Label>
                          {resolvedBranch ? (
                            <div className={`h-11 w-full rounded-xl border border-green-200 dark:border-green-900/50 bg-green-50/30 dark:bg-green-900/20 px-3 flex items-center text-sm text-gray-900 dark:text-white font-medium`}>
                              {resolvedBranch.name}
                            </div>
                          ) : (
                            <select
                              {...reg2('branch_id', { required: 'Please select a branch.' })}
                              className={`h-11 w-full rounded-xl border border-green-200 dark:border-green-900/50 bg-white dark:bg-[#0d1410] px-3 text-sm text-gray-900 dark:text-white focus:border-green-500 dark:focus:border-green-400 focus:ring-2 focus:ring-green-500/20 appearance-none disabled:opacity-60 disabled:cursor-not-allowed`}
                              disabled
                            >
                              <option value="">Select branch</option>
                              {branches.map((branch) => (
                                <option key={branch.branch_id} value={String(branch.branch_id)}>
                                  {branch.name}
                                </option>
                              ))}
                            </select>
                          )}
                          {err2.branch_id && <p className="text-xs text-red-500 font-medium">{err2.branch_id.message}</p>}
                        </div>

                        <div className="space-y-1.5">
                          <Label className={labelClass}>Remarks <span className="text-gray-400 dark:text-gray-600 normal-case font-medium">(optional)</span></Label>
                          <Textarea {...reg2('remarks')} placeholder="Any additional notes..." rows={3} className={`${inputClass} resize-none`} />
                        </div>
                      </CardContent>
                    </Card>
                  </div>
                </div>

                <div className="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-between gap-3 mt-6">
                  <button type="button" onClick={() => setStep(1)} className={`inline-flex items-center justify-center ${navButtonClass}`}>
                    <ChevronLeft className="w-4 h-4" /> Back
                  </button>
                  <button type="submit" className={`inline-flex items-center justify-center ${primaryButtonClass}`}>
                    Save & Continue <ArrowRight className="ml-2 w-4 h-4" />
                  </button>
                </div>
              </form>
            )}

            {/* ── STEP 3 ── */}
            {step === 3 && (
              <form onSubmit={handle3((data) => { setSpouseData(data); toast.success('Spouse information saved.'); })}>
                <Card className={cardClass}>
                  <div className={cardHeaderClass}>
                    <div className="flex items-center gap-3">
                      <div className="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center text-white font-black text-sm flex-shrink-0">3</div>
                      <div>
                        <p className="text-xs font-black text-white/70 uppercase tracking-widest">Step 3</p>
                        <h3 className="text-lg font-black text-white uppercase tracking-tight">Spouse & Co-Makers</h3>
                      </div>
                    </div>
                    <p className="text-sm text-white/70 mt-2 font-medium">Add spouse and co-maker information (optional).</p>
                  </div>

                  <CardContent className="space-y-6 p-6 sm:p-8">
                    {civilStatus === 'Married' && (
                      <>
                        <SectionDivider label="Spouse Information" />
                        <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                          <div className="space-y-1.5">
                            <Label className={labelClass}>Full Name</Label>
                            <Input {...reg3('full_name')} className={inputClass} />
                          </div>
                          <div className="space-y-1.5">
                            <Label className={labelClass}>Birthdate</Label>
                            <Input
                              type="date"
                              max={getMaxBirthdate(selectedTypeId)}
                              {...reg3('birthdate', {
                                validate: {
                                  notFuture: (value) => {
                                    if (!value) return true;
                                    const today = new Date().toISOString().split('T')[0];
                                    return value < today || 'Birthdate cannot be today or in the future';
                                  },
                                  ageRequirement: (value) => {
                                    if (!value) return true;
                                    const minAge = getMinimumAge(selectedTypeId);
                                    const age = calculateAge(value);
                                    return age >= minAge || `Spouse must be at least ${minAge} years old (age: ${age})`;
                                  },
                                },
                              })}
                              className={inputClass}
                            />
                          </div>
                          <div className="space-y-1.5">
                            <Label className={labelClass}>Occupation</Label>
                            <Input {...reg3('occupation')} className={inputClass} />
                          </div>
                        </div>
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                          <div className="space-y-1.5">
                            <Label className={labelClass}>Employer</Label>
                            <Input {...reg3('employer_name')} className={inputClass} />
                          </div>
                          <div className="space-y-1.5">
                            <Label className={labelClass}>Source of Income</Label>
                            <Input {...reg3('source_of_income')} className={inputClass} />
                          </div>
                        </div>
                        <div className="space-y-1.5">
                          <Label className={labelClass}>Monthly Income (₱)</Label>
                          <Input
                            inputMode="numeric"
                            {...reg3('monthly_income')}
                            className={inputClass}
                            onInput={(e) => {
                              const input = e.currentTarget;
                              input.value = digitsOnly(input.value);
                              set3('monthly_income', input.value);
                            }}
                            onPaste={(e) => e.preventDefault()}
                            onKeyDown={(e) => { if (!/[0-9]|Backspace|Delete|ArrowLeft|ArrowRight|Tab/.test(e.key)) e.preventDefault(); }}
                          />
                        </div>
                      </>
                    )}

                    <SectionDivider label="Co-Makers / Guarantors" />

                    <div className="space-y-4">
                      {coMakersData.map((coMaker, idx) => (
                        <div key={idx} className="border border-green-100 dark:border-green-900/40 rounded-2xl p-5 space-y-4 bg-green-50/20 dark:bg-green-900/10">
                          <div className="flex items-center justify-between">
                            <h4 className="font-black text-sm uppercase tracking-wide text-green-700 dark:text-green-400">Co-Maker {idx + 1}</h4>
                            <button
                              type="button"
                              onClick={() => setCoMakersData(coMakersData.filter((_, i) => i !== idx))}
                              className="text-xs font-black uppercase tracking-widest text-red-500 hover:text-red-700 border border-red-200 dark:border-red-900 rounded-full px-4 py-1.5 transition-colors"
                            >
                              Remove
                            </button>
                          </div>
                          <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div className="space-y-1.5">
                              <Label className={labelClass}>Full Name</Label>
                              <Input value={coMaker.full_name} onChange={(e) => { const u = [...coMakersData]; u[idx].full_name = e.target.value; setCoMakersData(u); }} className={inputClass} />
                            </div>
                            <div className="space-y-1.5">
                              <Label className={labelClass}>Relationship</Label>
                              <Input value={coMaker.relationship} onChange={(e) => { const u = [...coMakersData]; u[idx].relationship = e.target.value; setCoMakersData(u); }} className={inputClass} />
                            </div>
                          </div>
                          <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div className="space-y-1.5">
                              <Label className={labelClass}>Contact Number</Label>
                              <Input
                                inputMode="numeric"
                                placeholder="09XXXXXXXXX"
                                maxLength={11}
                                value={coMaker.contact_number}
                                onChange={(e) => {
                                  const val = phMobileFormat(e.target.value);
                                  const u = [...coMakersData]; u[idx].contact_number = val; setCoMakersData(u);
                                }}
                                onPaste={(e) => e.preventDefault()}
                                onKeyDown={(e) => { if (!/[0-9]|Backspace|Delete|ArrowLeft|ArrowRight|Tab/.test(e.key)) e.preventDefault(); }}
                                className={inputClass}
                              />
                            </div>
                            <div className="space-y-1.5">
                              <Label className={labelClass}>Occupation</Label>
                              <Input value={coMaker.occupation} onChange={(e) => { const u = [...coMakersData]; u[idx].occupation = e.target.value; setCoMakersData(u); }} className={inputClass} />
                            </div>
                          </div>
                          <div className="space-y-1.5">
                            <Label className={labelClass}>Address</Label>
                            <Input value={coMaker.address} onChange={(e) => { const u = [...coMakersData]; u[idx].address = e.target.value; setCoMakersData(u); }} className={inputClass} />
                          </div>
                          <div className="space-y-1.5">
                            <Label className={labelClass}>Employer</Label>
                            <Input value={coMaker.employer_name} onChange={(e) => { const u = [...coMakersData]; u[idx].employer_name = e.target.value; setCoMakersData(u); }} className={inputClass} />
                          </div>
                          <div className="space-y-1.5">
                            <Label className={labelClass}>Monthly Income (₱)</Label>
                            <Input
                              inputMode="numeric"
                              value={coMaker.monthly_income}
                              onChange={(e) => {
                                const val = digitsOnly(e.target.value);
                                const u = [...coMakersData]; u[idx].monthly_income = val; setCoMakersData(u);
                              }}
                              onPaste={(e) => e.preventDefault()}
                              onKeyDown={(e) => { if (!/[0-9]|Backspace|Delete|ArrowLeft|ArrowRight|Tab/.test(e.key)) e.preventDefault(); }}
                              className={inputClass}
                            />
                          </div>
                        </div>
                      ))}

                      <button
                        type="button"
                        onClick={() => setCoMakersData([...coMakersData, { full_name: '', relationship: '', contact_number: '', address: '', occupation: '', employer_name: '', monthly_income: '' }])}
                        className="w-full py-3 rounded-2xl border-2 border-dashed border-green-300 dark:border-green-800 text-green-600 dark:text-green-400 font-black uppercase tracking-widest text-xs hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors"
                      >
                        + Add Co-Maker
                      </button>
                    </div>
                  </CardContent>
                </Card>

                <div className="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-between gap-3 mt-6">
                  <button type="button" onClick={() => setStep(2)} className={`inline-flex items-center justify-center ${navButtonClass}`}>
                    <ChevronLeft className="w-4 h-4" /> Back
                  </button>
                  <button
                    type="button"
                    onClick={() => { setSpouseData(watch3() as SpouseData); toast.success('Spouse and co-maker information saved.'); setStep(4); }}
                    className={`inline-flex items-center justify-center ${primaryButtonClass}`}
                  >
                    Save & Continue to Orientation <ArrowRight className="ml-2 w-4 h-4" />
                  </button>
                </div>
              </form>
            )}

            {/* ── STEP 4 ── */}
            {step === 4 && (
              <div className="space-y-6">
                <Card className={cardClass}>
                  <div className={cardHeaderClass}>
                    <div className="flex items-center gap-3">
                      <div className="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center text-white font-black text-sm flex-shrink-0">4</div>
                      <div>
                        <p className="text-xs font-black text-white/70 uppercase tracking-widest">Step 4</p>
                        <h3 className="text-lg font-black text-white uppercase tracking-tight">Orientation</h3>
                      </div>
                    </div>
                    <p className="text-sm text-white/70 mt-2 font-medium">Complete the orientation before submitting your application.</p>
                  </div>

                  <CardContent className="space-y-6 p-6 sm:p-8">
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                      <div className="rounded-2xl border border-green-100 dark:border-green-900/40 p-5 bg-green-50/20 dark:bg-green-900/10">
                        <div className="flex items-center gap-2 mb-3">
                          <div className="w-8 h-8 rounded-xl bg-green-100 dark:bg-green-900/40 flex items-center justify-center">
                            <CalendarCheck className="w-4 h-4 text-green-600 dark:text-green-400" />
                          </div>
                          <h3 className="font-black uppercase tracking-wide text-sm text-gray-800 dark:text-gray-200">Zoom Orientation</h3>
                        </div>
                        <p className="text-sm text-gray-500 dark:text-gray-400 font-medium mb-4">Attend the Zoom pre-membership orientation.</p>
                        {orientationSettings.zoom_link ? (
                          <button
                            onClick={handleZoomClick}
                            className={`w-full inline-flex items-center justify-center ${zoomClicked ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 border-2 border-green-300 dark:border-green-700 rounded-full font-black uppercase tracking-widest text-xs py-3' : primaryButtonClass}`}
                          >
                            {zoomClicked ? '✓ Zoom Link Accessed' : 'Join Zoom Orientation'}
                          </button>
                        ) : (
                          <div className="w-full rounded-2xl border border-green-100 dark:border-green-900/40 bg-green-50/30 dark:bg-green-900/10 flex items-center justify-center text-sm text-gray-400 dark:text-gray-600 px-4 py-6 text-center font-medium">
                            No Zoom link configured yet.
                          </div>
                        )}
                        <p className="text-xs text-gray-400 dark:text-gray-600 mt-3 font-medium">
                          {zoomClicked ? '✓ Thank you for attending the Zoom orientation.' : 'Click the button above to join the Zoom meeting.'}
                        </p>
                      </div>

                      <div className="rounded-2xl border border-green-100 dark:border-green-900/40 p-5 bg-green-50/20 dark:bg-green-900/10">
                        <div className="flex items-center gap-2 mb-3">
                          <div className="w-8 h-8 rounded-xl bg-green-100 dark:bg-green-900/40 flex items-center justify-center">
                            <Video className="w-4 h-4 text-green-600 dark:text-green-400" />
                          </div>
                          <h3 className="font-black uppercase tracking-wide text-sm text-gray-800 dark:text-gray-200">Orientation Video</h3>
                        </div>
                        <p className="text-sm text-gray-500 dark:text-gray-400 font-medium mb-4">Watch the orientation video completely.</p>
                        {orientationSettings.video_link ? (
                          <div className="space-y-3">
                            <div className="w-full bg-black rounded-2xl overflow-hidden" style={{ aspectRatio: '16/9' }}>
                              <div id="orientation-video-player" className="w-full h-full" />
                            </div>
                            {videoInteracted ? (
                              <div className="w-full rounded-2xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 px-3 py-2.5 text-center text-sm text-green-700 dark:text-green-400 font-black uppercase tracking-widest">
                                ✓ Video Watched
                              </div>
                            ) : (
                              <div className="w-full rounded-2xl bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-900/40 px-3 py-2.5 text-center text-xs text-amber-700 dark:text-amber-400 font-medium">
                                Watch until the end to mark as watched
                              </div>
                            )}
                          </div>
                        ) : (
                          <div className="w-full h-40 rounded-2xl border border-green-100 dark:border-green-900/40 bg-green-50/30 flex items-center justify-center text-sm text-gray-400 dark:text-gray-600 px-4 text-center font-medium">
                            No video link configured yet.
                          </div>
                        )}
                      </div>
                    </div>

                    <div className="rounded-2xl border border-green-100 dark:border-green-900/40 p-5 bg-green-50/20 dark:bg-green-900/10">
                      <div className="flex items-center gap-2 mb-3">
                        <div className="w-8 h-8 rounded-xl bg-green-100 dark:bg-green-900/40 flex items-center justify-center">
                          <FileBadge className="w-4 h-4 text-green-600 dark:text-green-400" />
                        </div>
                        <h3 className="font-black uppercase tracking-wide text-sm text-gray-800 dark:text-gray-200">Post-Orientation Assessment</h3>
                      </div>
                      <p className="text-sm text-gray-500 dark:text-gray-400 mb-5 font-medium">
                        Passing score: <strong className="text-green-700 dark:text-green-400 font-black">{orientationSettings.passing_score}%</strong>
                      </p>
                      <div className="space-y-5">
                        {normalizedQuestions.length === 0 && (
                          <div className="rounded-2xl border border-green-100 dark:border-green-900/40 bg-green-50/30 px-4 py-4 text-sm text-gray-400 dark:text-gray-600 font-medium">
                            No assessment questions configured yet.
                          </div>
                        )}
                        {normalizedQuestions.map((question: any, index: number) => (
                          <div key={index} className="space-y-2">
                            <Label className={labelClass}>{index + 1}. {question.question}</Label>
                            <Select
                              onValueChange={(value) => { if (!assessmentSubmitted) setAssessmentAnswers((prev) => ({ ...prev, [index]: value })); }}
                              value={assessmentAnswers[index] ?? ''}
                              disabled={assessmentSubmitted}
                            >
                              <SelectTrigger className={inputClass}><SelectValue placeholder="Select your answer" /></SelectTrigger>
                              <SelectContent>
                                {question.choices.map((choice: string) => <SelectItem key={choice} value={choice}>{choice}</SelectItem>)}
                              </SelectContent>
                            </Select>
                          </div>
                        ))}
                      </div>
                      {!assessmentSubmitted && normalizedQuestions.length > 0 && (
                        <div className="mt-6 rounded-2xl border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 p-5">
                          <p className="text-sm text-gray-500 dark:text-gray-400 font-medium mb-4">
                            Answer all {normalizedQuestions.length} questions above, then submit to see your score.
                          </p>
                          <button
                            onClick={submitAssessment}
                            disabled={!allQuestionsAnswered}
                            className={`w-full inline-flex items-center justify-center ${allQuestionsAnswered ? primaryButtonClass : 'rounded-full bg-gray-200 dark:bg-gray-800 text-gray-400 dark:text-gray-600 font-black uppercase tracking-widest text-xs px-8 py-3 cursor-not-allowed'}`}
                          >
                            {allQuestionsAnswered ? 'Submit Assessment' : `Answer all questions (${Object.keys(assessmentAnswers).length}/${normalizedQuestions.length})`}
                          </button>
                        </div>
                      )}
                      {assessmentSubmitted && (
                        <div className={`mt-6 rounded-2xl border p-5 ${assessmentPassed ? 'border-green-300 dark:border-green-700 bg-green-50 dark:bg-green-900/20' : 'border-red-200 dark:border-red-900 bg-red-50/30 dark:bg-red-900/10'}`}>
                          <div className="flex items-center justify-between mb-2">
                            <span className="font-black uppercase tracking-widest text-xs text-gray-600 dark:text-gray-400">Your Score</span>
                            <span className={`font-black text-xl ${assessmentPassed ? 'text-green-700 dark:text-green-400' : 'text-red-600 dark:text-red-400'}`}>{assessmentScore}%</span>
                          </div>
                          <div className="flex items-center justify-between">
                            <span className="font-black uppercase tracking-widest text-xs text-gray-600 dark:text-gray-400">Result</span>
                            <span className={`font-black text-sm ${assessmentPassed ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'}`}>
                              {assessmentPassed ? '✓ Passed' : '✗ Did not pass'}
                            </span>
                          </div>
                          {!assessmentPassed && (
                            <p className="text-xs text-gray-500 dark:text-gray-400 font-medium mt-3">
                              You need {orientationSettings.passing_score}% to pass. Please review your answers.
                            </p>
                          )}
                        </div>
                      )}
                    </div>

                    <div className="rounded-2xl border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 p-5">
                      <p className="font-black uppercase tracking-widest text-xs text-green-700 dark:text-green-400 mb-4">Orientation Checklist</p>
                      <div className="space-y-3">
                        {[
                          { label: 'Zoom Pre-Membership Orientation', done: zoomClicked },
                          { label: 'Orientation Video', done: videoInteracted },
                          { label: 'Assessment', done: assessmentSubmitted && assessmentPassed, failed: assessmentSubmitted && !assessmentPassed },
                          { label: 'Certificate Generation', done: zoomClicked && videoInteracted && assessmentSubmitted && assessmentPassed },
                        ].map(({ label, done, failed }) => (
                          <div key={label} className="flex items-center justify-between gap-2">
                            <span className="text-sm font-medium text-gray-600 dark:text-gray-400">{label}</span>
                            <span className={`flex-shrink-0 font-black text-xs uppercase tracking-widest ${done ? 'text-green-600 dark:text-green-400' : failed ? 'text-red-500' : 'text-gray-400 dark:text-gray-600'}`}>
                              {done ? '✓ Done' : failed ? '✗ Failed' : 'Pending'}
                            </span>
                          </div>
                        ))}
                      </div>
                    </div>

                    {!orientationComplete && (
                      <div className="rounded-2xl border border-amber-300 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/10 px-5 py-4 text-sm text-amber-900 dark:text-amber-400 font-medium">
                        Submit Application will stay disabled until orientation is completed and the assessment passing score is met.
                      </div>
                    )}
                    {orientationComplete && (
                      <div className="rounded-2xl border border-green-300 dark:border-green-700 bg-green-50 dark:bg-green-900/20 px-5 py-4 text-sm text-green-800 dark:text-green-400 font-medium">
                        ✓ Orientation completed. You can now submit the application.
                      </div>
                    )}
                  </CardContent>
                </Card>

                <div className="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-between gap-3">
                  <button type="button" onClick={() => setStep(3)} className={`inline-flex items-center justify-center ${navButtonClass}`}>
                    <ChevronLeft className="w-4 h-4" /> Back
                  </button>
                  <button
                    type="button"
                    disabled={!orientationComplete || saving}
                    onClick={submitFinalApplication}
                    className={`inline-flex items-center justify-center ${orientationComplete && !saving ? primaryButtonClass : 'rounded-full bg-gray-200 dark:bg-gray-800 text-gray-400 dark:text-gray-600 font-black uppercase tracking-widest text-xs px-8 py-3 cursor-not-allowed'}`}
                  >
                    {saving ? (
                      <><Loader2 className="w-4 h-4 mr-2 animate-spin" />Submitting...</>
                    ) : (
                      <>Submit Application <ArrowRight className="ml-2 w-4 h-4" /></>
                    )}
                  </button>
                </div>
              </div>
            )}

          </div>
        </section>
      </div>
    </>
  );
}

/* ─── Hero Section ─── */
function HeroSection({ heroVisible, membershipTypeLabel }: { heroVisible: boolean; membershipTypeLabel: string }) {
  return (
    <section className="relative min-h-[50dvh] sm:min-h-[60dvh] flex items-center justify-center overflow-hidden">
      <div
        className="absolute inset-0 bg-[url('/src/images/bghd.jpg')] bg-cover bg-center"
        style={{ transition: 'transform 20s linear', transform: heroVisible ? 'scale(1)' : 'scale(1.05)' }}
      />
      <div className="absolute inset-0 bg-gradient-to-br from-white/90 via-green-50/80 to-green-100/90 dark:from-[#022c22]/95 dark:via-[#064e3b]/95 dark:to-[#065f46]/95 transition-colors duration-500" />
      <Particles />
      <div
        className={`relative z-10 max-w-7xl mx-auto px-6 text-center transition-all duration-1000 ${heroVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'}`}
      >
        <div className="inline-flex items-center gap-2 mb-6 px-4 py-2 rounded-full bg-green-200/50 dark:bg-white/10 border border-green-300 dark:border-white/20 backdrop-blur-md">
          <div className="w-2.5 h-2.5 bg-green-600 dark:bg-green-400 rounded-full animate-pulse" />
          <span className="text-xs sm:text-sm text-green-900 dark:text-white/90 font-medium uppercase tracking-widest">Application Form</span>
        </div>
        <h1 className="text-4xl sm:text-6xl font-extrabold mb-4 uppercase tracking-tight text-gray-900 dark:text-white leading-[0.95]">
          Membership{' '}
          <span className="bg-clip-text text-transparent bg-gradient-to-r from-green-700 to-green-500 dark:from-green-400 dark:to-green-200">
            Application
          </span>
        </h1>
        {membershipTypeLabel && (
          <p className="text-base sm:text-lg text-gray-600 dark:text-white/70 mb-2 font-medium">
            Applying as:{' '}
            <span className="text-green-700 dark:text-green-400 font-black uppercase tracking-wide">{membershipTypeLabel}</span>
          </p>
        )}
        <p className="text-base sm:text-lg text-gray-700 dark:text-white/70 max-w-xl mx-auto font-medium leading-relaxed">
          Complete the form below to begin your journey with us.
        </p>
      </div>
    </section>
  );
}